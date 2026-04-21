<?php

namespace App\Services;

use App\DTOs\ProjectDTO;
use App\Models\Project;
use App\Models\AuditLog;
use App\Repositories\ProjectRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepository $projectRepo
    ) {}

    public function list(int $companyId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->projectRepo->paginateForCompany($companyId, $filters, $perPage);
    }

    public function create(ProjectDTO $dto, int $companyId, int $userId): Project
    {
        return DB::transaction(function () use ($dto, $companyId, $userId) {
            $project = $this->projectRepo->create(array_merge(
                $dto->toArray(),
                ['company_id' => $companyId, 'created_by' => $userId]
            ));

            AuditLog::log('project.created', $project, [], $project->toArray());

            return $project;
        });
    }

    public function show(int $id, int $companyId): Project
    {
        return $this->projectRepo->findForCompany($id, $companyId);
    }

    public function update(Project $project, ProjectDTO $dto): Project
    {
        return DB::transaction(function () use ($project, $dto) {
            $old = $project->toArray();
            $updated = $this->projectRepo->update($project, $dto->toArray());
            AuditLog::log('project.updated', $updated, $old, $updated->toArray());
            return $updated;
        });
    }

    public function delete(Project $project): void
    {
        DB::transaction(function () use ($project) {
            AuditLog::log('project.deleted', $project, $project->toArray(), []);
            $this->projectRepo->delete($project);
        });
    }

    public function assignMember(Project $project, int $userId, string $role): void
    {
        $project->members()->updateOrCreate(
            ['user_id' => $userId],
            ['role' => $role, 'assigned_at' => now()]
        );
    }

    public function removeMember(Project $project, int $userId): void
    {
        $project->members()->where('user_id', $userId)->delete();
    }

    public function getDashboardSummary(int $companyId): array
    {
        return $this->projectRepo->getDashboardSummary($companyId);
    }
}

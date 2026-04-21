<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectRepository extends BaseRepository
{
    public function __construct(Project $model)
    {
        parent::__construct($model);
    }

    public function paginateForCompany(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('company_id', $companyId)
            ->with(['creator', 'members.user']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%")
                  ->orWhere('client_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findForCompany(int $id, int $companyId): Project
    {
        return $this->model
            ->where('company_id', $companyId)
            ->with(['creator', 'members.user', 'milestones', 'boqItems'])
            ->findOrFail($id);
    }

    public function getDashboardSummary(int $companyId): array
    {
        $projects = $this->model->where('company_id', $companyId);

        return [
            'total'       => $projects->count(),
            'planned'     => $projects->clone()->where('status', 'planned')->count(),
            'in_progress' => $projects->clone()->where('status', 'in_progress')->count(),
            'on_hold'     => $projects->clone()->where('status', 'on_hold')->count(),
            'completed'   => $projects->clone()->where('status', 'completed')->count(),
        ];
    }
}

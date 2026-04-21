<?php

namespace App\Repositories;

use App\Models\Indent;
use Illuminate\Pagination\LengthAwarePaginator;

class IndentRepository extends BaseRepository
{
    public function __construct(Indent $model)
    {
        parent::__construct($model);
    }

    public function paginateForCompany(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('company_id', $companyId)
            ->with(['project', 'requester', 'approver', 'items.material']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('indent_number', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function findForCompany(int $id, int $companyId): Indent
    {
        return $this->model
            ->where('company_id', $companyId)
            ->with(['project', 'requester', 'approver', 'items.material'])
            ->findOrFail($id);
    }

    public function generateNumber(int $companyId): string
    {
        $count = $this->model->where('company_id', $companyId)->count() + 1;
        return 'IND-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Repositories;

use App\Models\Subcontractor;
use Illuminate\Pagination\LengthAwarePaginator;

class SubcontractorRepository extends BaseRepository
{
    public function __construct(Subcontractor $model)
    {
        parent::__construct($model);
    }

    public function paginateForCompany(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->where('company_id', $companyId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('specialization', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findForCompany(int $id, int $companyId): Subcontractor
    {
        return $this->model
            ->where('company_id', $companyId)
            ->with(['contracts.project', 'bills'])
            ->findOrFail($id);
    }
}

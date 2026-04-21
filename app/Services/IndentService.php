<?php

namespace App\Services;

use App\DTOs\IndentDTO;
use App\Models\Indent;
use App\Models\AuditLog;
use App\Repositories\IndentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IndentService
{
    public function __construct(
        private readonly IndentRepository $indentRepo
    ) {}

    public function list(int $companyId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->indentRepo->paginateForCompany($companyId, $filters, $perPage);
    }

    public function create(IndentDTO $dto, int $companyId, int $userId): Indent
    {
        return DB::transaction(function () use ($dto, $companyId, $userId) {
            $indent = $this->indentRepo->create([
                'company_id'       => $companyId,
                'project_id'       => $dto->projectId,
                'indent_number'    => $this->indentRepo->generateNumber($companyId),
                'indent_date'      => $dto->indentDate,
                'required_by_date' => $dto->requiredByDate,
                'remarks'          => $dto->remarks,
                'status'           => 'draft',
                'requested_by'     => $userId,
            ]);

            foreach ($dto->items as $item) {
                $indent->items()->create([
                    'material_id'    => $item['material_id'],
                    'quantity'       => $item['quantity'],
                    'unit'           => $item['unit'],
                    'specifications' => $item['specifications'] ?? null,
                ]);
            }

            AuditLog::log('indent.created', $indent, [], $indent->toArray());

            return $indent->load(['items.material', 'project', 'requester']);
        });
    }

    public function update(Indent $indent, IndentDTO $dto): Indent
    {
        return DB::transaction(function () use ($indent, $dto) {
            $old = $indent->toArray();

            $indent->update([
                'project_id'       => $dto->projectId,
                'indent_date'      => $dto->indentDate,
                'required_by_date' => $dto->requiredByDate,
                'remarks'          => $dto->remarks,
            ]);

            $indent->items()->delete();

            foreach ($dto->items as $item) {
                $indent->items()->create([
                    'material_id'    => $item['material_id'],
                    'quantity'       => $item['quantity'],
                    'unit'           => $item['unit'],
                    'specifications' => $item['specifications'] ?? null,
                ]);
            }

            AuditLog::log('indent.updated', $indent, $old, $indent->fresh()->toArray());

            return $indent->load(['items.material', 'project', 'requester']);
        });
    }

    public function show(int $id, int $companyId): Indent
    {
        return $this->indentRepo->findForCompany($id, $companyId);
    }

    public function submit(Indent $indent): Indent
    {
        if ($indent->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Only draft indents can be submitted.']);
        }

        $indent->update(['status' => 'submitted']);
        AuditLog::log('indent.submitted', $indent, ['status' => 'draft'], ['status' => 'submitted']);

        return $indent;
    }

    public function approve(Indent $indent, int $approverId): Indent
    {
        if ($indent->status !== 'submitted') {
            throw ValidationException::withMessages(['status' => 'Only submitted indents can be approved.']);
        }

        $indent->update([
            'status'      => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        AuditLog::log('indent.approved', $indent, ['status' => 'submitted'], ['status' => 'approved']);

        return $indent;
    }

    public function reject(Indent $indent, string $reason): Indent
    {
        if (!in_array($indent->status, ['submitted', 'draft'])) {
            throw ValidationException::withMessages(['status' => 'This indent cannot be rejected.']);
        }

        $indent->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);

        AuditLog::log('indent.rejected', $indent, ['status' => $indent->status], ['status' => 'rejected']);

        return $indent;
    }

    public function delete(Indent $indent): void
    {
        if (!in_array($indent->status, ['draft', 'rejected'])) {
            throw ValidationException::withMessages(['status' => 'Only draft or rejected indents can be deleted.']);
        }

        DB::transaction(function () use ($indent) {
            $indent->items()->delete();
            $this->indentRepo->delete($indent);
        });
    }
}

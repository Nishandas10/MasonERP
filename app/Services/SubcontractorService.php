<?php

namespace App\Services;

use App\Models\Subcontractor;
use App\Models\SubcontractorContract;
use App\Models\SubcontractorBill;
use App\Models\AuditLog;
use App\Repositories\SubcontractorRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubcontractorService
{
    public function __construct(
        private readonly SubcontractorRepository $repo
    ) {}

    public function list(int $companyId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginateForCompany($companyId, $filters, $perPage);
    }

    public function create(array $data, int $companyId): Subcontractor
    {
        return DB::transaction(function () use ($data, $companyId) {
            $subcontractor = $this->repo->create(array_merge($data, ['company_id' => $companyId]));
            AuditLog::log('subcontractor.created', $subcontractor, [], $subcontractor->toArray());
            return $subcontractor;
        });
    }

    public function show(int $id, int $companyId): Subcontractor
    {
        return $this->repo->findForCompany($id, $companyId);
    }

    public function update(Subcontractor $subcontractor, array $data): Subcontractor
    {
        return DB::transaction(function () use ($subcontractor, $data) {
            $old = $subcontractor->toArray();
            $updated = $this->repo->update($subcontractor, $data);
            AuditLog::log('subcontractor.updated', $updated, $old, $updated->toArray());
            return $updated;
        });
    }

    public function delete(Subcontractor $subcontractor): void
    {
        $activeContracts = $subcontractor->contracts()->where('status', 'active')->count();
        if ($activeContracts > 0) {
            throw ValidationException::withMessages([
                'subcontractor' => 'Cannot delete a subcontractor with active contracts.',
            ]);
        }

        DB::transaction(function () use ($subcontractor) {
            AuditLog::log('subcontractor.deleted', $subcontractor, $subcontractor->toArray(), []);
            $this->repo->delete($subcontractor);
        });
    }

    public function createContract(Subcontractor $subcontractor, array $data, int $companyId): SubcontractorContract
    {
        return DB::transaction(function () use ($subcontractor, $data, $companyId) {
            $count = SubcontractorContract::where('company_id', $companyId)->count() + 1;
            $data['contract_number'] = 'SC-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $data['company_id'] = $companyId;
            $data['subcontractor_id'] = $subcontractor->id;

            $contract = SubcontractorContract::create($data);
            AuditLog::log('subcontractor_contract.created', $contract, [], $contract->toArray());

            return $contract->load('project');
        });
    }

    public function createBill(SubcontractorContract $contract, array $data, int $companyId): SubcontractorBill
    {
        return DB::transaction(function () use ($contract, $data, $companyId) {
            $count = SubcontractorBill::where('company_id', $companyId)->count() + 1;
            $billNumber = 'SCB-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $retentionAmount = ($data['gross_amount'] * $contract->retention_percent) / 100;
            $netPayable = $data['gross_amount'] - $retentionAmount - ($data['tax_deducted'] ?? 0) - ($data['other_deductions'] ?? 0);

            $bill = SubcontractorBill::create(array_merge($data, [
                'company_id'                  => $companyId,
                'subcontractor_contract_id'   => $contract->id,
                'subcontractor_id'            => $contract->subcontractor_id,
                'bill_number'                 => $billNumber,
                'retention_amount'            => $retentionAmount,
                'net_payable'                 => $netPayable,
                'status'                      => 'pending',
            ]));

            AuditLog::log('subcontractor_bill.created', $bill, [], $bill->toArray());

            return $bill->load('subcontractor', 'contract');
        });
    }

    public function approveBill(SubcontractorBill $bill, int $approverId): SubcontractorBill
    {
        if ($bill->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only pending bills can be approved.']);
        }

        $bill->update(['status' => 'approved', 'approved_by' => $approverId]);
        return $bill;
    }

    public function recordPayment(SubcontractorBill $bill, float $amount, string $reference, string $date): SubcontractorBill
    {
        if (!in_array($bill->status, ['approved', 'partially_paid'])) {
            throw ValidationException::withMessages(['status' => 'Bill must be approved before recording payment.']);
        }

        $totalPaid = $bill->paid_amount + $amount;
        $status = $totalPaid >= $bill->net_payable ? 'paid' : 'partially_paid';

        $bill->update([
            'paid_amount'        => $totalPaid,
            'status'             => $status,
            'payment_date'       => $date,
            'payment_reference'  => $reference,
        ]);

        return $bill;
    }
}

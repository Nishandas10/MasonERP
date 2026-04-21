<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subcontractor;
use App\Models\SubcontractorContract;
use App\Models\SubcontractorBill;
use App\Services\SubcontractorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubcontractorController extends Controller
{
    public function __construct(private readonly SubcontractorService $service) {}

    public function index(Request $request): JsonResponse
    {
        $list = $this->service->list(
            $request->user()->company_id,
            $request->only(['status', 'search']),
            $request->integer('per_page', 15)
        );

        return response()->json(['data' => $list]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'nullable|string|max:50',
            'contact_person'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email',
            'address'         => 'nullable|string',
            'gstin'           => 'nullable|string|max:20',
            'pan'             => 'nullable|string|max:15',
            'specialization'  => 'nullable|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'bank_account'    => 'nullable|string|max:30',
            'bank_ifsc'       => 'nullable|string|max:15',
        ]);

        $subcontractor = $this->service->create($validated, $request->user()->company_id);

        return response()->json(['message' => 'Subcontractor created.', 'data' => $subcontractor], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $subcontractor = $this->service->show($id, $request->user()->company_id);

        return response()->json(['data' => $subcontractor]);
    }

    public function update(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $this->authorizeSubcontractor($subcontractor, $request->user()->company_id);

        $validated = $request->validate([
            'name'            => 'sometimes|required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email',
            'address'         => 'nullable|string',
            'gstin'           => 'nullable|string|max:20',
            'pan'             => 'nullable|string|max:15',
            'specialization'  => 'nullable|string|max:255',
            'bank_name'       => 'nullable|string|max:255',
            'bank_account'    => 'nullable|string|max:30',
            'bank_ifsc'       => 'nullable|string|max:15',
            'status'          => 'nullable|in:active,inactive,blacklisted',
        ]);

        $updated = $this->service->update($subcontractor, $validated);

        return response()->json(['message' => 'Subcontractor updated.', 'data' => $updated]);
    }

    public function destroy(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $this->authorizeSubcontractor($subcontractor, $request->user()->company_id);
        $this->service->delete($subcontractor);

        return response()->json(['message' => 'Subcontractor deleted.']);
    }

    // --- Contracts ---
    public function contracts(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $this->authorizeSubcontractor($subcontractor, $request->user()->company_id);

        $contracts = $subcontractor->contracts()
            ->with('project')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $contracts]);
    }

    public function storeContract(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $this->authorizeSubcontractor($subcontractor, $request->user()->company_id);

        $validated = $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after:start_date',
            'scope_of_work'   => 'required|string',
            'contract_value'  => 'required|numeric|min:0',
            'payment_terms'   => 'nullable|string',
            'retention_percent' => 'nullable|integer|between:0,100',
        ]);

        $contract = $this->service->createContract($subcontractor, $validated, $request->user()->company_id);

        return response()->json(['message' => 'Contract created.', 'data' => $contract], 201);
    }

    // --- Bills ---
    public function bills(Request $request, Subcontractor $subcontractor): JsonResponse
    {
        $this->authorizeSubcontractor($subcontractor, $request->user()->company_id);

        $bills = $subcontractor->bills()
            ->with('contract.project')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $bills]);
    }

    public function storeBill(Request $request, SubcontractorContract $contract): JsonResponse
    {
        abort_if($contract->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'bill_date'        => 'required|date',
            'description'      => 'required|string',
            'gross_amount'     => 'required|numeric|min:0',
            'tax_deducted'     => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
        ]);

        $bill = $this->service->createBill($contract, $validated, $request->user()->company_id);

        return response()->json(['message' => 'Bill created.', 'data' => $bill], 201);
    }

    public function approveBill(Request $request, SubcontractorBill $bill): JsonResponse
    {
        abort_if($bill->company_id !== $request->user()->company_id, 403);
        $bill = $this->service->approveBill($bill, $request->user()->id);

        return response()->json(['message' => 'Bill approved.', 'data' => $bill]);
    }

    public function recordPayment(Request $request, SubcontractorBill $bill): JsonResponse
    {
        abort_if($bill->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'amount'     => 'required|numeric|min:0.01',
            'reference'  => 'required|string|max:100',
            'date'       => 'required|date',
        ]);

        $bill = $this->service->recordPayment(
            $bill,
            $validated['amount'],
            $validated['reference'],
            $validated['date']
        );

        return response()->json(['message' => 'Payment recorded.', 'data' => $bill]);
    }

    private function authorizeSubcontractor(Subcontractor $subcontractor, int $companyId): void
    {
        abort_if($subcontractor->company_id !== $companyId, 403, 'Unauthorized.');
    }
}

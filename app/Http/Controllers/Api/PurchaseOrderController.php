<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = PurchaseOrder::where('company_id', $request->user()->company_id)
            ->with(['vendor', 'project', 'items.material'])
            ->when($request->vendor_id, fn($q) => $q->where('vendor_id', $request->vendor_id))
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('po_date')
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id'            => 'required|exists:vendors,id',
            'project_id'           => 'required|exists:projects,id',
            'indent_id'            => 'nullable|exists:indents,id',
            'po_date'              => 'required|date',
            'delivery_date'        => 'nullable|date|after_or_equal:po_date',
            'delivery_address'     => 'nullable|string|max:500',
            'terms_and_conditions' => 'nullable|string',
            'status'               => 'nullable|in:draft,sent,acknowledged,partially_received,received,cancelled',
            'items'                => 'required|array|min:1',
            'items.*.material_id'  => 'required|exists:materials,id',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit'         => 'required|string|max:30',
            'items.*.rate'         => 'required|numeric|min:0',
            'items.*.tax_percent'  => 'nullable|numeric|min:0|max:100',
            'items.*.received_quantity' => 'nullable|numeric|min:0',
        ]);

        $po = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $taxTotal = 0;
            foreach ($validated['items'] as $item) {
                $lineAmt = $item['quantity'] * $item['rate'];
                $subtotal += $lineAmt;
                $taxTotal += $lineAmt * (($item['tax_percent'] ?? 0) / 100);
            }

            $poNumber = 'PO-' . now()->format('Y') . '-' . str_pad(
                PurchaseOrder::where('company_id', $request->user()->company_id)->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $po = PurchaseOrder::create([
                'company_id'           => $request->user()->company_id,
                'vendor_id'            => $validated['vendor_id'],
                'project_id'           => $validated['project_id'],
                'indent_id'            => $validated['indent_id'] ?? null,
                'po_number'            => $poNumber,
                'po_date'              => $validated['po_date'],
                'delivery_date'        => $validated['delivery_date'] ?? null,
                'delivery_address'     => $validated['delivery_address'] ?? null,
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'subtotal'             => $subtotal,
                'tax_amount'           => $taxTotal,
                'total_amount'         => $subtotal + $taxTotal,
                'status'               => $validated['status'] ?? 'draft',
                'created_by'           => $request->user()->id,
            ]);

            foreach ($validated['items'] as $item) {
                $po->items()->create([
                    'material_id'       => $item['material_id'],
                    'quantity'          => $item['quantity'],
                    'unit'              => $item['unit'],
                    'rate'              => $item['rate'],
                    'tax_percent'       => $item['tax_percent'] ?? 0,
                    'received_quantity' => $item['received_quantity'] ?? 0,
                ]);
            }

            return $po->load(['vendor', 'project', 'items.material']);
        });

        return response()->json(['message' => 'Purchase order created.', 'data' => $po], 201);
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        abort_if($purchaseOrder->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $purchaseOrder->load(['vendor', 'project', 'items.material'])]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        abort_if($purchaseOrder->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'vendor_id'            => 'sometimes|required|exists:vendors,id',
            'project_id'           => 'sometimes|required|exists:projects,id',
            'po_date'              => 'sometimes|required|date',
            'delivery_date'        => 'nullable|date',
            'delivery_address'     => 'nullable|string|max:500',
            'terms_and_conditions' => 'nullable|string',
            'status'               => 'nullable|in:draft,sent,acknowledged,partially_received,received,cancelled',
            'items'                => 'sometimes|required|array|min:1',
            'items.*.material_id'  => 'required_with:items|exists:materials,id',
            'items.*.quantity'     => 'required_with:items|numeric|min:0.001',
            'items.*.unit'         => 'required_with:items|string|max:30',
            'items.*.rate'         => 'required_with:items|numeric|min:0',
            'items.*.tax_percent'  => 'nullable|numeric|min:0|max:100',
            'items.*.received_quantity' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            if (isset($validated['items'])) {
                $subtotal = 0;
                $taxTotal = 0;
                foreach ($validated['items'] as $item) {
                    $lineAmt = $item['quantity'] * $item['rate'];
                    $subtotal += $lineAmt;
                    $taxTotal += $lineAmt * (($item['tax_percent'] ?? 0) / 100);
                }
                $validated['subtotal']      = $subtotal;
                $validated['tax_amount']    = $taxTotal;
                $validated['total_amount']  = $subtotal + $taxTotal;

                $purchaseOrder->items()->delete();
                foreach ($validated['items'] as $item) {
                    $purchaseOrder->items()->create([
                        'material_id'       => $item['material_id'],
                        'quantity'          => $item['quantity'],
                        'unit'              => $item['unit'],
                        'rate'              => $item['rate'],
                        'tax_percent'       => $item['tax_percent'] ?? 0,
                        'received_quantity' => $item['received_quantity'] ?? 0,
                    ]);
                }
                unset($validated['items']);
            }

            $purchaseOrder->update($validated);
        });

        return response()->json(['message' => 'Purchase order updated.', 'data' => $purchaseOrder->load(['vendor', 'project', 'items.material'])]);
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        abort_if($purchaseOrder->company_id !== $request->user()->company_id, 403);
        abort_if(!in_array($purchaseOrder->status, ['draft', 'cancelled']), 422, 'Only draft or cancelled POs can be deleted.');

        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase order deleted.']);
    }
}

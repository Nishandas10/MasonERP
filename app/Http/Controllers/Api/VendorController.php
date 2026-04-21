<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vendors = Vendor::where('company_id', $request->user()->company_id)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $vendors]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'nullable|string|max:50',
            'contact_person'  => 'nullable|string|max:255',
            'email'           => 'nullable|email',
            'phone'           => 'nullable|string|max:20',
            'address'         => 'nullable|string',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:100',
            'gstin'           => 'nullable|string|max:20',
            'pan'             => 'nullable|string|max:15',
            'bank_name'       => 'nullable|string|max:255',
            'bank_account'    => 'nullable|string|max:30',
            'bank_ifsc'       => 'nullable|string|max:15',
        ]);

        $vendor = Vendor::create(array_merge($validated, ['company_id' => $request->user()->company_id]));

        return response()->json(['message' => 'Vendor created.', 'data' => $vendor], 201);
    }

    public function show(Request $request, Vendor $vendor): JsonResponse
    {
        abort_if($vendor->company_id !== $request->user()->company_id, 403);

        $vendor->load([
            'purchaseOrders' => fn($q) => $q->with(['items.material', 'project'])->latest('po_date'),
        ]);

        $orders = $vendor->purchaseOrders;

        $totalOrdered   = $orders->sum(fn($o) => floatval($o->total_amount));
        $totalReceived  = $orders
            ->whereIn('status', ['received', 'partially_received'])
            ->sum(fn($o) => floatval($o->total_amount));
        $pendingOrders  = $orders
            ->whereNotIn('status', ['received', 'cancelled'])
            ->count();
        $totalCancelled = $orders
            ->where('status', 'cancelled')
            ->sum(fn($o) => floatval($o->total_amount));
        $amountDue = max(0, $totalOrdered - $totalReceived - $totalCancelled);

        return response()->json([
            'data' => $vendor,
            'stats' => [
                'total_orders'   => $orders->count(),
                'total_ordered'  => $totalOrdered,
                'total_received' => $totalReceived,
                'pending_orders' => $pendingOrders,
                'amount_due'     => $amountDue,
            ],
        ]);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        abort_if($vendor->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'gstin'          => 'nullable|string|max:20',
            'pan'            => 'nullable|string|max:15',
            'bank_name'      => 'nullable|string|max:255',
            'bank_account'   => 'nullable|string|max:30',
            'bank_ifsc'      => 'nullable|string|max:15',
            'status'         => 'nullable|in:active,inactive,blacklisted',
        ]);

        $vendor->update($validated);

        return response()->json(['message' => 'Vendor updated.', 'data' => $vendor]);
    }

    public function destroy(Request $request, Vendor $vendor): JsonResponse
    {
        abort_if($vendor->company_id !== $request->user()->company_id, 403);
        $vendor->delete();

        return response()->json(['message' => 'Vendor deleted.']);
    }
}

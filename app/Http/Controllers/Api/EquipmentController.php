<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentAssignment;
use App\Models\MaintenanceLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $equipment = Equipment::where('company_id', $request->user()->company_id)
            ->with('currentAssignment.project')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $equipment]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'nullable|string|max:50',
            'type'                => 'required|string|max:100',
            'make'                => 'nullable|string|max:100',
            'model'               => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:30',
            'purchase_date'       => 'nullable|date',
            'purchase_value'      => 'nullable|numeric|min:0',
            'ownership'           => 'nullable|in:owned,rented,leased',
            'rental_rate_per_day' => 'nullable|numeric|min:0',
        ]);

        $equipment = Equipment::create(array_merge($validated, ['company_id' => $request->user()->company_id]));

        return response()->json(['message' => 'Equipment created.', 'data' => $equipment], 201);
    }

    public function show(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if($equipment->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $equipment->load('assignments.project', 'maintenanceLogs')]);
    }

    public function update(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if($equipment->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'   => 'sometimes|required|string|max:255',
            'status' => 'nullable|in:available,deployed,maintenance,breakdown,retired',
        ]);

        $equipment->update($validated);

        return response()->json(['message' => 'Equipment updated.', 'data' => $equipment]);
    }

    public function assign(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if($equipment->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'assigned_date' => 'required|date',
            'remarks'       => 'nullable|string',
        ]);

        $assignment = EquipmentAssignment::create(array_merge($validated, [
            'equipment_id' => $equipment->id,
            'assigned_by'  => $request->user()->id,
        ]));

        $equipment->update(['status' => 'deployed']);

        return response()->json(['message' => 'Equipment assigned.', 'data' => $assignment], 201);
    }

    public function release(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if($equipment->company_id !== $request->user()->company_id, 403);

        $assignment = $equipment->currentAssignment;
        abort_if(!$assignment, 400, 'Equipment is not currently assigned.');

        $assignment->update(['released_date' => now()->toDateString()]);
        $equipment->update(['status' => 'available']);

        return response()->json(['message' => 'Equipment released.']);
    }

    public function maintenance(Request $request, Equipment $equipment): JsonResponse
    {
        abort_if($equipment->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'maintenance_date'      => 'required|date',
            'type'                  => 'required|in:scheduled,breakdown,preventive',
            'description'           => 'required|string',
            'cost'                  => 'nullable|numeric|min:0',
            'done_by'               => 'nullable|string|max:255',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
        ]);

        $log = $equipment->maintenanceLogs()->create($validated);

        return response()->json(['message' => 'Maintenance log added.', 'data' => $log], 201);
    }
}

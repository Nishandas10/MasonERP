<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laborer;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LaborController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $laborers = Laborer::where('company_id', $request->user()->company_id)
            ->when($request->trade, fn($q) => $q->where('trade', $request->trade))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $laborers]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'nullable|string|max:50',
            'phone'             => 'nullable|string|max:20',
            'trade'             => 'required|string|max:100',
            'daily_rate'        => 'nullable|numeric|min:0',
            'aadhaar'           => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
        ]);

        $laborer = Laborer::create(array_merge($validated, ['company_id' => $request->user()->company_id]));

        return response()->json(['message' => 'Laborer created.', 'data' => $laborer], 201);
    }

    public function show(Request $request, Laborer $laborer): JsonResponse
    {
        abort_if($laborer->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $laborer->load('attendance')]);
    }

    public function update(Request $request, Laborer $laborer): JsonResponse
    {
        abort_if($laborer->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'trade'      => 'sometimes|required|string|max:100',
            'daily_rate' => 'nullable|numeric|min:0',
            'status'     => 'nullable|in:active,inactive',
        ]);

        $laborer->update($validated);

        return response()->json(['message' => 'Laborer updated.', 'data' => $laborer]);
    }

    public function markAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'date'          => 'required|date',
            'attendance'    => 'required|array|min:1',
            'attendance.*.laborer_id'    => 'required|exists:laborers,id',
            'attendance.*.status'        => 'required|in:present,absent,half_day,overtime',
            'attendance.*.hours_worked'  => 'nullable|numeric|min:0',
            'attendance.*.overtime_hours'=> 'nullable|numeric|min:0',
        ]);

        $companyId = $request->user()->company_id;
        $records = [];

        foreach ($validated['attendance'] as $record) {
            $records[] = Attendance::updateOrCreate(
                [
                    'project_id' => $validated['project_id'],
                    'laborer_id' => $record['laborer_id'],
                    'date'       => $validated['date'],
                ],
                [
                    'company_id'     => $companyId,
                    'status'         => $record['status'],
                    'hours_worked'   => $record['hours_worked'] ?? 8,
                    'overtime_hours' => $record['overtime_hours'] ?? 0,
                    'marked_by'      => $request->user()->id,
                ]
            );
        }

        return response()->json(['message' => 'Attendance marked.', 'data' => $records]);
    }

    public function getAttendance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'date'       => 'required|date',
        ]);

        $attendance = Attendance::where('project_id', $validated['project_id'])
            ->where('date', $validated['date'])
            ->where('company_id', $request->user()->company_id)
            ->with('laborer')
            ->get();

        return response()->json(['data' => $attendance]);
    }
}

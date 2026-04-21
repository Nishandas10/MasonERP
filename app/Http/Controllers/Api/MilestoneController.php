<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\BoqItem;
use App\Models\SiteLog;
use App\Models\WorkProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $project->milestones()->latest()->get()]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'due_date'         => 'required|date',
            'status'           => 'nullable|in:pending,in_progress,completed,delayed',
            'progress_percent' => 'nullable|integer|between:0,100',
        ]);

        $milestone = $project->milestones()->create($validated);

        return response()->json(['message' => 'Milestone created.', 'data' => $milestone], 201);
    }

    public function update(Request $request, Project $project, Milestone $milestone): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'description'      => 'nullable|string',
            'due_date'         => 'nullable|date',
            'completed_date'   => 'nullable|date',
            'status'           => 'nullable|in:pending,in_progress,completed,delayed',
            'progress_percent' => 'nullable|integer|between:0,100',
        ]);

        $milestone->update($validated);

        return response()->json(['message' => 'Milestone updated.', 'data' => $milestone]);
    }

    public function destroy(Request $request, Project $project, Milestone $milestone): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);
        $milestone->delete();

        return response()->json(['message' => 'Milestone deleted.']);
    }
}

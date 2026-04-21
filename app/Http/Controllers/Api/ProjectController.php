<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ProjectDTO;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search']);
        $projects = $this->projectService->list(
            $request->user()->company_id,
            $filters,
            $request->integer('per_page', 15)
        );

        return response()->json(['data' => $projects]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'client_name'    => 'nullable|string|max:255',
            'client_contact' => 'nullable|string|max:255',
            'location'       => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'contract_value' => 'nullable|numeric|min:0',
            'budget'         => 'nullable|numeric|min:0',
            'status'         => 'nullable|in:planned,in_progress,on_hold,completed,cancelled',
        ]);

        $project = $this->projectService->create(
            ProjectDTO::fromRequest($validated),
            $request->user()->company_id,
            $request->user()->id
        );

        return response()->json(['message' => 'Project created.', 'data' => $project], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $project = $this->projectService->show($id, $request->user()->company_id);

        return response()->json(['data' => $project]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($project, $request->user()->company_id);

        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'code'             => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'client_name'      => 'nullable|string|max:255',
            'client_contact'   => 'nullable|string|max:255',
            'location'         => 'nullable|string|max:255',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'contract_value'   => 'nullable|numeric|min:0',
            'budget'           => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:planned,in_progress,on_hold,completed,cancelled',
            'progress_percent' => 'nullable|integer|between:0,100',
        ]);

        $updated = $this->projectService->update($project, ProjectDTO::fromRequest($validated));

        return response()->json(['message' => 'Project updated.', 'data' => $updated]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($project, $request->user()->company_id);
        $this->projectService->delete($project);

        return response()->json(['message' => 'Project deleted.']);
    }

    public function assignMember(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($project, $request->user()->company_id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|in:project_manager,site_engineer,accountant,member',
        ]);

        $this->projectService->assignMember($project, $validated['user_id'], $validated['role']);

        return response()->json(['message' => 'Member assigned.']);
    }

    public function removeMember(Request $request, Project $project, int $userId): JsonResponse
    {
        $this->authorizeProject($project, $request->user()->company_id);
        $this->projectService->removeMember($project, $userId);

        return response()->json(['message' => 'Member removed.']);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $summary = $this->projectService->getDashboardSummary($request->user()->company_id);

        return response()->json(['data' => $summary]);
    }

    private function authorizeProject(Project $project, int $companyId): void
    {
        abort_if($project->company_id !== $companyId, 403, 'Unauthorized.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\DTOs\IndentDTO;
use App\Http\Controllers\Controller;
use App\Models\Indent;
use App\Services\IndentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IndentController extends Controller
{
    public function __construct(private readonly IndentService $indentService) {}

    public function index(Request $request): JsonResponse
    {
        $indents = $this->indentService->list(
            $request->user()->company_id,
            $request->only(['status', 'project_id', 'search']),
            $request->integer('per_page', 15)
        );

        return response()->json(['data' => $indents]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'indent_date'      => 'required|date',
            'required_by_date' => 'nullable|date|after_or_equal:indent_date',
            'remarks'          => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.material_id'    => 'required|exists:materials,id',
            'items.*.quantity'       => 'required|numeric|min:0.001',
            'items.*.unit'           => 'required|string|max:30',
            'items.*.specifications' => 'nullable|string',
        ]);

        $indent = $this->indentService->create(
            IndentDTO::fromRequest($validated),
            $request->user()->company_id,
            $request->user()->id
        );

        return response()->json(['message' => 'Indent created.', 'data' => $indent], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $indent = $this->indentService->show($id, $request->user()->company_id);

        return response()->json(['data' => $indent]);
    }

    public function update(Request $request, Indent $indent): JsonResponse
    {
        $this->authorizeIndent($indent, $request->user()->company_id);

        abort_if($indent->status !== 'draft', 422, 'Only draft indents can be edited.');

        $validated = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'indent_date'      => 'required|date',
            'required_by_date' => 'nullable|date|after_or_equal:indent_date',
            'remarks'          => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.material_id'    => 'required|exists:materials,id',
            'items.*.quantity'       => 'required|numeric|min:0.001',
            'items.*.unit'           => 'required|string|max:30',
            'items.*.specifications' => 'nullable|string',
        ]);

        $indent = $this->indentService->update($indent, IndentDTO::fromRequest($validated));

        return response()->json(['message' => 'Indent updated.', 'data' => $indent]);
    }

    public function submit(Request $request, Indent $indent): JsonResponse
    {
        $this->authorizeIndent($indent, $request->user()->company_id);
        $indent = $this->indentService->submit($indent);

        return response()->json(['message' => 'Indent submitted for approval.', 'data' => $indent]);
    }

    public function approve(Request $request, Indent $indent): JsonResponse
    {
        $this->authorizeIndent($indent, $request->user()->company_id);
        $indent = $this->indentService->approve($indent, $request->user()->id);

        return response()->json(['message' => 'Indent approved.', 'data' => $indent]);
    }

    public function reject(Request $request, Indent $indent): JsonResponse
    {
        $this->authorizeIndent($indent, $request->user()->company_id);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $indent = $this->indentService->reject($indent, $validated['reason']);

        return response()->json(['message' => 'Indent rejected.', 'data' => $indent]);
    }

    public function destroy(Request $request, Indent $indent): JsonResponse
    {
        $this->authorizeIndent($indent, $request->user()->company_id);
        $this->indentService->delete($indent);

        return response()->json(['message' => 'Indent deleted.']);
    }

    private function authorizeIndent(Indent $indent, int $companyId): void
    {
        abort_if($indent->company_id !== $companyId, 403, 'Unauthorized.');
    }
}

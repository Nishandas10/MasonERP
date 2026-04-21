<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\BoqItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoqController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $project->boqItems()->get()]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'items'                    => 'required|array|min:1',
            'items.*.item_code'        => 'nullable|string|max:50',
            'items.*.description'      => 'required|string',
            'items.*.unit'             => 'required|string|max:20',
            'items.*.quantity'         => 'required|numeric|min:0.001',
            'items.*.rate'             => 'required|numeric|min:0',
            'items.*.category'         => 'nullable|string|max:100',
        ]);

        $items = collect($validated['items'])->map(fn($item) => array_merge($item, ['project_id' => $project->id]));
        BoqItem::insert($items->toArray());

        return response()->json(['message' => 'BOQ items added.', 'data' => $project->boqItems()->get()], 201);
    }

    public function update(Request $request, Project $project, BoqItem $boqItem): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);
        abort_if($boqItem->project_id !== $project->id, 404);

        $validated = $request->validate([
            'description' => 'sometimes|required|string',
            'unit'        => 'sometimes|required|string|max:20',
            'quantity'    => 'sometimes|required|numeric|min:0.001',
            'rate'        => 'sometimes|required|numeric|min:0',
            'category'    => 'nullable|string|max:100',
        ]);

        $boqItem->update($validated);

        return response()->json(['message' => 'BOQ item updated.', 'data' => $boqItem]);
    }

    public function destroy(Request $request, Project $project, BoqItem $boqItem): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);
        abort_if($boqItem->project_id !== $project->id, 404);
        $boqItem->delete();

        return response()->json(['message' => 'BOQ item deleted.']);
    }
}

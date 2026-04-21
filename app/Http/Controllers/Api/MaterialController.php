<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $materials = Material::where('company_id', $request->user()->company_id)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $materials]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:materials,code',
            'unit'          => 'required|string|max:30',
            'category'      => 'nullable|string|max:100',
            'standard_rate' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock'     => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
        ]);

        $material = Material::create(array_merge($validated, ['company_id' => $request->user()->company_id]));

        return response()->json(['message' => 'Material created.', 'data' => $material], 201);
    }

    public function show(Request $request, Material $material): JsonResponse
    {
        abort_if($material->company_id !== $request->user()->company_id, 403);

        return response()->json(['data' => $material]);
    }

    public function update(Request $request, Material $material): JsonResponse
    {
        abort_if($material->company_id !== $request->user()->company_id, 403);

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'unit'          => 'sometimes|required|string|max:30',
            'category'      => 'nullable|string|max:100',
            'standard_rate' => 'nullable|numeric|min:0',
            'current_stock' => 'nullable|numeric|min:0',
            'min_stock'     => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:active,inactive',
        ]);

        $material->update($validated);

        return response()->json(['message' => 'Material updated.', 'data' => $material]);
    }

    public function destroy(Request $request, Material $material): JsonResponse
    {
        abort_if($material->company_id !== $request->user()->company_id, 403);
        $material->delete();

        return response()->json(['message' => 'Material deleted.']);
    }
}

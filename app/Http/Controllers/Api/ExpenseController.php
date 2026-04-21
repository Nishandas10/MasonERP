<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $categories = ExpenseCategory::where('company_id', $request->user()->company_id)->get();

        return response()->json(['data' => $categories]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:material,labor,equipment,overhead,operational',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['company_id'] = $request->user()->company_id;

        $category = ExpenseCategory::create($validated);

        return response()->json(['message' => 'Category created.', 'data' => $category], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $expenses = Expense::where('company_id', $request->user()->company_id)
            ->with(['category', 'project', 'creator'])
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from_date, fn($q) => $q->where('expense_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->where('expense_date', '<=', $request->to_date))
            ->latest('expense_date')
            ->paginate($request->integer('per_page', 15));

        return response()->json(['data' => $expenses]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string',
            'amount'              => 'required|numeric|min:0.01',
            'expense_date'        => 'required|date',
            'reference_number'    => 'nullable|string|max:100',
            'payment_mode'        => 'nullable|in:cash,bank,upi,cheque',
        ]);

        $expense = Expense::create(array_merge($validated, [
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
            'status'     => 'pending',
        ]));

        return response()->json(['message' => 'Expense created.', 'data' => $expense->load('category')], 201);
    }

    public function approve(Request $request, Expense $expense): JsonResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);

        $expense->update(['status' => 'approved', 'approved_by' => $request->user()->id]);

        return response()->json(['message' => 'Expense approved.', 'data' => $expense]);
    }

    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        abort_if($expense->company_id !== $request->user()->company_id, 403);
        $expense->delete();

        return response()->json(['message' => 'Expense deleted.']);
    }
}

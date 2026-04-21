<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\Indent;
use App\Models\Laborer;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\SubcontractorBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id;

        $projectsQuery = Project::where('company_id', $companyId);

        $data = [
            'projects' => [
                'total'       => (clone $projectsQuery)->count(),
                'in_progress' => (clone $projectsQuery)->where('status', 'in_progress')->count(),
                'completed'   => (clone $projectsQuery)->where('status', 'completed')->count(),
                'on_hold'     => (clone $projectsQuery)->where('status', 'on_hold')->count(),
                'planned'     => (clone $projectsQuery)->where('status', 'planned')->count(),
                'total_budget'=> (clone $projectsQuery)->sum('budget'),
            ],
            'procurement' => [
                'pending_indents' => Indent::where('company_id', $companyId)->where('status', 'submitted')->count(),
                'open_pos'        => PurchaseOrder::where('company_id', $companyId)
                    ->whereIn('status', ['sent', 'acknowledged', 'partially_received'])->count(),
            ],
            'finance' => [
                'pending_bills'    => SubcontractorBill::where('company_id', $companyId)->where('status', 'pending')->count(),
                'pending_expenses' => Expense::where('company_id', $companyId)->where('status', 'pending')->count(),
                'total_expenses'   => Expense::where('company_id', $companyId)->where('status', 'approved')->sum('amount'),
            ],
            'labor' => [
                'total'  => Laborer::where('company_id', $companyId)->count(),
                'active' => Laborer::where('company_id', $companyId)->where('status', 'active')->count(),
            ],
            'equipment' => [
                'total'      => Equipment::where('company_id', $companyId)->count(),
                'available'  => Equipment::where('company_id', $companyId)->where('status', 'available')->count(),
                'deployed'   => Equipment::where('company_id', $companyId)->where('status', 'deployed')->count(),
            ],
            'recent_projects' => Project::where('company_id', $companyId)
                ->with('creator')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'client_name', 'status', 'progress_percent', 'budget', 'end_date', 'created_by']),
        ];

        return response()->json(['data' => $data]);
    }

    public function projectCostSummary(Request $request, Project $project): JsonResponse
    {
        abort_if($project->company_id !== $request->user()->company_id, 403);

        $boqTotal = $project->boqItems()->sum(DB::raw('quantity * rate'));
        $expensesTotal = $project->expenses()->where('status', 'approved')->sum('amount');
        $subBillsTotal = $project->subcontractorContracts()
            ->withSum('bills', 'net_payable')
            ->get()
            ->sum('bills_sum_net_payable');

        $poTotal = $project->purchaseOrders()->whereIn('status', ['received', 'partially_received'])->sum('total_amount');

        return response()->json([
            'data' => [
                'budget'          => $project->budget,
                'contract_value'  => $project->contract_value,
                'boq_total'       => $boqTotal,
                'expenses_total'  => $expensesTotal,
                'po_total'        => $poTotal,
                'subcontract_total' => $subBillsTotal,
                'total_cost'      => $expensesTotal + $poTotal + $subBillsTotal,
                'budget_variance' => $project->budget - ($expensesTotal + $poTotal + $subBillsTotal),
            ],
        ]);
    }
}

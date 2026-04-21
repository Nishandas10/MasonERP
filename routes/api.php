<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoqController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IndentController;
use App\Http\Controllers\Api\LaborController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\MilestoneController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SubcontractorController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mason ERP — API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ─── Auth (public) ───────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login',    [AuthController::class, 'login']);
    });

    // ─── Protected Routes ─────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('me',              [AuthController::class, 'me']);
            Route::post('logout',         [AuthController::class, 'logout']);
            Route::post('logout-all',     [AuthController::class, 'logoutAll']);
            Route::post('refresh',        [AuthController::class, 'refresh']);
            Route::post('change-password',[AuthController::class, 'changePassword']);
        });

        // Dashboard
        Route::get('dashboard',                          [DashboardController::class, 'summary']);
        Route::get('dashboard/project/{project}/cost',   [DashboardController::class, 'projectCostSummary']);

        // Users
        Route::apiResource('users', UserController::class);

        // Projects
        Route::apiResource('projects', ProjectController::class);
        Route::get('projects/dashboard/summary', [ProjectController::class, 'dashboard']);
        Route::post('projects/{project}/members',              [ProjectController::class, 'assignMember']);
        Route::delete('projects/{project}/members/{userId}',   [ProjectController::class, 'removeMember']);

        // Milestones
        Route::apiResource('projects.milestones', MilestoneController::class)->shallow();

        // BOQ
        Route::get('projects/{project}/boq',             [BoqController::class, 'index']);
        Route::post('projects/{project}/boq',            [BoqController::class, 'store']);
        Route::put('projects/{project}/boq/{boqItem}',   [BoqController::class, 'update']);
        Route::delete('projects/{project}/boq/{boqItem}',[BoqController::class, 'destroy']);

        // Materials
        Route::apiResource('materials', MaterialController::class);

        // Vendors
        Route::apiResource('vendors', VendorController::class);

        // Purchase Orders
        Route::apiResource('purchase-orders', PurchaseOrderController::class);

        // Indents
        Route::apiResource('indents', IndentController::class);
        Route::post('indents/{indent}/submit',  [IndentController::class, 'submit']);
        Route::post('indents/{indent}/approve', [IndentController::class, 'approve']);
        Route::post('indents/{indent}/reject',  [IndentController::class, 'reject']);

        // Labor & Attendance
        Route::apiResource('laborers', LaborController::class)->except(['destroy']);
        Route::post('attendance',     [LaborController::class, 'markAttendance']);
        Route::get('attendance',      [LaborController::class, 'getAttendance']);

        // Equipment
        Route::apiResource('equipment', EquipmentController::class)->except(['destroy']);
        Route::post('equipment/{equipment}/assign',      [EquipmentController::class, 'assign']);
        Route::post('equipment/{equipment}/release',     [EquipmentController::class, 'release']);
        Route::post('equipment/{equipment}/maintenance', [EquipmentController::class, 'maintenance']);

        // Subcontractors
        Route::apiResource('subcontractors', SubcontractorController::class);
        Route::get('subcontractors/{subcontractor}/contracts',        [SubcontractorController::class, 'contracts']);
        Route::post('subcontractors/{subcontractor}/contracts',       [SubcontractorController::class, 'storeContract']);
        Route::get('subcontractors/{subcontractor}/bills',            [SubcontractorController::class, 'bills']);
        Route::post('subcontractor-contracts/{contract}/bills',       [SubcontractorController::class, 'storeBill']);
        Route::post('subcontractor-bills/{bill}/approve',             [SubcontractorController::class, 'approveBill']);
        Route::post('subcontractor-bills/{bill}/payment',             [SubcontractorController::class, 'recordPayment']);

        // Expenses
        Route::get('expense-categories',       [ExpenseController::class, 'categories']);
        Route::post('expense-categories',      [ExpenseController::class, 'storeCategory']);
        Route::apiResource('expenses', ExpenseController::class)->except(['show', 'update']);
        Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve']);
    });
});

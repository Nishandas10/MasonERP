<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_contact')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->decimal('budget', 15, 2)->default(0);
            $table->enum('status', ['planned', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('planned');
            $table->tinyInteger('progress_percent')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->unique(['company_id', 'code']);
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member'); // project_manager, site_engineer, accountant, member
            $table->date('assigned_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed'])->default('pending');
            $table->tinyInteger('progress_percent')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('item_code', 50)->nullable();
            $table->string('description');
            $table->string('unit', 20);
            $table->decimal('quantity', 12, 3);
            $table->decimal('rate', 12, 2);
            $table->decimal('amount', 15, 2)->storedAs('quantity * rate');
            $table->decimal('consumed_quantity', 12, 3)->default(0);
            $table->string('category')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });

        Schema::create('site_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('logged_by')->constrained('users');
            $table->date('log_date');
            $table->text('work_done');
            $table->text('issues')->nullable();
            $table->integer('labor_count')->default(0);
            $table->text('remarks')->nullable();
            $table->json('weather_conditions')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'log_date']);
        });

        Schema::create('work_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boq_item_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('quantity_done', 12, 3);
            $table->foreignId('logged_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_progress');
        Schema::dropIfExists('site_logs');
        Schema::dropIfExists('boq_items');
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};

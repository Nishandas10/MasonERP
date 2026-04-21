<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laborers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('trade'); // mason, carpenter, plumber, electrician, helper, etc.
            $table->decimal('daily_rate', 10, 2)->default(0);
            $table->string('aadhaar', 20)->nullable();
            $table->string('emergency_contact')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'trade']);
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('laborer_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'half_day', 'overtime'])->default('present');
            $table->decimal('hours_worked', 5, 2)->default(8);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'laborer_id', 'date']);
            $table->index(['project_id', 'date']);
        });

        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->string('activity');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->timestamps();

            $table->index(['project_id', 'user_id', 'date']);
        });

        Schema::create('subcontractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('pan', 15)->nullable();
            $table->string('specialization')->nullable(); // Civil, MEP, Finishing, etc.
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->string('bank_ifsc', 15)->nullable();
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('subcontractor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcontractor_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('scope_of_work');
            $table->decimal('contract_value', 15, 2);
            $table->string('payment_terms')->nullable();
            $table->integer('retention_percent')->default(0);
            $table->enum('status', ['draft', 'active', 'completed', 'terminated'])->default('draft');
            $table->string('document_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'subcontractor_id']);
        });

        Schema::create('subcontractor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcontractor_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subcontractor_id')->constrained()->cascadeOnDelete();
            $table->string('bill_number', 50)->unique();
            $table->date('bill_date');
            $table->text('description');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('retention_amount', 15, 2)->default(0);
            $table->decimal('tax_deducted', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('net_payable', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'partially_paid', 'paid', 'rejected'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractor_bills');
        Schema::dropIfExists('subcontractor_contracts');
        Schema::dropIfExists('subcontractors');
        Schema::dropIfExists('timesheets');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('laborers');
    }
};

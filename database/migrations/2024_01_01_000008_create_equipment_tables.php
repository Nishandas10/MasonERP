<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('type'); // crane, excavator, mixer, etc.
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('registration_number', 30)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_value', 15, 2)->default(0);
            $table->enum('ownership', ['owned', 'rented', 'leased'])->default('owned');
            $table->decimal('rental_rate_per_day', 10, 2)->default(0);
            $table->enum('status', ['available', 'deployed', 'maintenance', 'breakdown', 'retired'])->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('equipment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_date');
            $table->date('released_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();

            $table->index(['equipment_id', 'project_id']);
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->date('maintenance_date');
            $table->enum('type', ['scheduled', 'breakdown', 'preventive'])->default('scheduled');
            $table->text('description');
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('done_by')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();

            $table->index(['equipment_id', 'maintenance_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('equipment_assignments');
        Schema::dropIfExists('equipment');
    }
};

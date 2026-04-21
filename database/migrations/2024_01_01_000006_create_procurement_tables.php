<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('pan', 15)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 30)->nullable();
            $table->string('bank_ifsc', 15)->nullable();
            $table->enum('status', ['active', 'inactive', 'blacklisted'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('unit', 30);
            $table->string('category')->nullable();
            $table->decimal('standard_rate', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'category']);
        });

        Schema::create('indents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('indent_number', 50)->unique();
            $table->date('indent_date');
            $table->date('required_by_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'partially_ordered', 'ordered'])->default('draft');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('project_id');
        });

        Schema::create('indent_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 30);
            $table->text('specifications')->nullable();
            $table->decimal('ordered_quantity', 12, 3)->default(0);
            $table->timestamps();

            $table->index('indent_id');
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('indent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('po_number', 50)->unique();
            $table->date('po_date');
            $table->date('delivery_date')->nullable();
            $table->string('delivery_address')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'acknowledged', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 30);
            $table->decimal('rate', 12, 2);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->storedAs('quantity * rate');
            $table->decimal('received_quantity', 12, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('grn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('grn_number', 50)->unique();
            $table->date('received_date');
            $table->string('delivery_note_number', 50)->nullable();
            $table->string('vehicle_number', 20)->nullable();
            $table->enum('status', ['pending', 'inspected', 'accepted', 'partially_accepted', 'rejected'])->default('pending');
            $table->foreignId('received_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('grn')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('received_quantity', 12, 3);
            $table->decimal('accepted_quantity', 12, 3)->default(0);
            $table->decimal('rejected_quantity', 12, 3)->default(0);
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('grn');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('indent_items');
        Schema::dropIfExists('indents');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('vendors');
    }
};

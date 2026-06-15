<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('measurement_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('short_name', 10);
            $table->string('base_unit', 50)->nullable();
            $table->decimal('conversion_factor', 12, 4)->default(1.0000);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['restaurant_id', 'short_name']);
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('grocery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('measurement_unit_id')->constrained('measurement_units')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('name', 100);
            $table->string('sku', 50)->nullable();
            $table->decimal('current_stock', 12, 4)->default(0.0000);
            $table->decimal('low_stock_threshold', 12, 4)->default(0.0000);
            $table->decimal('reorder_quantity', 12, 4)->nullable();
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->decimal('avg_cost_per_unit', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id']);
        });

        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->string('batch_number', 50)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->decimal('initial_quantity', 12, 4);
            $table->decimal('current_quantity', 12, 4);
            $table->decimal('unit_cost', 10, 2);
            $table->date('received_date');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained('item_variants')->onDelete('set null');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->foreignId('measurement_unit_id')->constrained('measurement_units')->onDelete('cascade');
            $table->decimal('quantity_required', 12, 4);
            $table->integer('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['menu_item_id', 'item_variant_id', 'is_current']);
        });

        Schema::create('recipe_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->json('snapshot');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->string('change_reason', 255)->nullable();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['menu_item_id', 'effective_from']);
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->enum('type', ['addition', 'order_fulfillment', 'order_cancellation', 'spoilage', 'adjustment', 'transfer', 'opening_balance', 'purchase_receipt', 'waste']);
            $table->decimal('quantity', 12, 4); // positive or negative
            $table->decimal('balance_after', 12, 4);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['grocery_item_id', 'created_at']);
            $table->index(['restaurant_id', 'branch_id', 'type', 'created_at'], 'inventory_transactions_log_index');
        });

        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->foreignId('measurement_unit_id')->constrained('measurement_units')->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->enum('reason', ['expired', 'spoilage', 'kitchen_mistake', 'returned', 'overproduction', 'other']);
            $table->string('reason_notes', 255)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('shift_id')->nullable(); // set in shift migration
            $table->timestamp('created_at')->nullable();

            $table->index(['restaurant_id', 'reason', 'created_at']);
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('po_number', 50)->unique();
            $table->enum('status', ['draft', 'sent', 'partial', 'received', 'cancelled'])->default('draft');
            $table->foreignId('ordered_by')->constrained('users')->onDelete('cascade');
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['restaurant_id', 'supplier_id', 'status']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->foreignId('measurement_unit_id')->constrained('measurement_units')->onDelete('cascade');
            $table->decimal('ordered_quantity', 12, 4);
            $table->decimal('received_quantity', 12, 4)->default(0.0000);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->date('receipt_date');
            $table->foreignId('approval_request_id')->nullable()->constrained('approval_requests')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->decimal('quantity_received', 12, 4);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('batch_number', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('quality_status', ['accepted', 'rejected', 'partial'])->default('accepted');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('from_branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('to_branch_id')->constrained('branches')->onDelete('cascade');
            $table->enum('status', ['pending', 'in_transit', 'received', 'cancelled'])->default('pending');
            $table->foreignId('transferred_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->decimal('received_quantity', 12, 4)->default(0.0000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('waste_records');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('recipe_versions');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('inventory_batches');
        Schema::dropIfExists('grocery_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('measurement_units');
    }
};

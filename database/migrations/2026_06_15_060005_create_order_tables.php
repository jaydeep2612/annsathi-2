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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('customer_session_id')->nullable()->constrained('customer_sessions')->onDelete('set null');
            $table->enum('service_type', ['dine_in', 'room_service', 'parcel', 'manual']);
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'waived', 'refunded'])->default('unpaid');
            $table->foreignId('parent_order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->boolean('is_merged')->default(false);
            $table->unsignedBigInteger('bill_group_id')->nullable(); // FK defined below
            $table->foreignId('assigned_waiter_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('customer_name', 100)->nullable();
            $table->text('notes')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->enum('discount_type', ['flat', 'percent'])->nullable();
            $table->decimal('discount_value', 8, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('extra_charges', 10, 2)->default(0.00);
            $table->string('extra_charges_label', 100)->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable(); // FK set in shift migration
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'status']);
            $table->index(['restaurant_id', 'branch_id', 'payment_status']);
            $table->index(['restaurant_id', 'branch_id', 'created_at']);
            $table->index(['customer_session_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->onDelete('set null');
            $table->string('item_name', 150);
            $table->string('item_variant_label', 80)->nullable();
            $table->foreignId('selected_variant_id')->nullable()->constrained('item_variants')->onDelete('set null');
            $table->decimal('unit_price', 10, 2);
            $table->smallInteger('quantity')->default(1);
            $table->decimal('total_price', 10, 2);
            $table->enum('item_nature', ['premade', 'readymade', 'made_to_order'])->default('made_to_order');
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'cancelled'])->default('pending');
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        // Set missing foreign keys on kitchen tables now that order tables exist
        Schema::table('kitchen_queue', function (Blueprint $table) {
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::table('order_item_kitchen_status', function (Blueprint $table) {
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
        });

        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('from_status', 30);
            $table->string('to_status', 30);
            $table->string('notes', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['order_id']);
        });

        Schema::create('bill_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('customer_session_id')->constrained('customer_sessions')->onDelete('cascade');
            $table->string('name', 100)->nullable();
            $table->decimal('target_amount', 10, 2)->nullable();
            $table->timestamps();
        });

        // Add bill_group_id foreign key constraint back to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('bill_group_id')->references('id')->on('bill_groups')->onDelete('set null');
        });

        Schema::create('bill_group_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_group_id')->constrained('bill_groups')->onDelete('cascade');
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->decimal('quantity', 8, 2);
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->unique(['bill_group_id', 'order_item_id']);
        });

        Schema::create('order_merge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('parent_order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('merged_order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('merged_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_merge_logs');
        Schema::dropIfExists('bill_group_items');
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['bill_group_id']);
        });

        Schema::dropIfExists('bill_groups');
        Schema::dropIfExists('order_status_logs');
        
        Schema::table('order_item_kitchen_status', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
        });

        Schema::table('kitchen_queue', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

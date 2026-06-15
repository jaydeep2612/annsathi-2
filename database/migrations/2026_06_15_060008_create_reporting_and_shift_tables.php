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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('shift_type', ['morning', 'afternoon', 'evening', 'night', 'custom'])->default('custom');
            $table->foreignId('started_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('ended_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->enum('status', ['open', 'closing', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'status']);
            $table->index(['restaurant_id', 'branch_id', 'start_time']);
        });

        // Set missing foreign key constraints now that shifts exist
        Schema::table('customer_sessions', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });

        Schema::table('cash_drawers', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
        });

        Schema::table('waste_records', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });

        Schema::create('shift_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('role_id')->nullable(); // Spatie role table reference
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();

            $table->unique(['shift_id', 'user_id']);
        });

        Schema::create('daily_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->date('summary_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->integer('total_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->decimal('gross_revenue', 12, 2)->default(0.00);
            $table->decimal('discount_total', 12, 2)->default(0.00);
            $table->decimal('tax_total', 12, 2)->default(0.00);
            $table->decimal('extra_charges_total', 12, 2)->default(0.00);
            $table->decimal('net_revenue', 12, 2)->default(0.00);
            $table->decimal('cash_collected', 12, 2)->default(0.00);
            $table->decimal('upi_collected', 12, 2)->default(0.00);
            $table->decimal('card_collected', 12, 2)->default(0.00);
            $table->decimal('complimentary_total', 12, 2)->default(0.00);
            $table->decimal('avg_order_value', 10, 2)->default(0.00);
            $table->tinyInteger('peak_hour')->nullable();
            $table->integer('dine_in_orders')->default(0);
            $table->integer('room_service_orders')->default(0);
            $table->integer('parcel_orders')->default(0);
            $table->integer('manual_orders')->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['restaurant_id', 'branch_id', 'summary_date', 'shift_id'], 'sales_summary_unique');
            $table->index(['restaurant_id', 'summary_date']);
        });

        Schema::create('daily_inventory_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->date('summary_date');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->decimal('opening_stock', 12, 4);
            $table->decimal('additions', 12, 4)->default(0.0000);
            $table->decimal('consumed', 12, 4)->default(0.0000);
            $table->decimal('waste', 12, 4)->default(0.0000);
            $table->decimal('adjustments', 12, 4)->default(0.0000);
            $table->decimal('closing_stock', 12, 4);
            $table->decimal('waste_cost', 10, 2)->default(0.00);
            $table->decimal('consumption_cost', 10, 2)->default(0.00);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['restaurant_id', 'branch_id', 'summary_date', 'grocery_item_id'], 'inv_summary_unique');
        });

        Schema::create('item_sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->date('summary_date');
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('item_variant_id')->nullable()->constrained('item_variants')->onDelete('set null');
            $table->integer('quantity_sold')->default(0);
            $table->decimal('gross_revenue', 12, 2)->default(0.00);
            $table->decimal('food_cost', 12, 2)->default(0.00);
            $table->decimal('gross_profit', 12, 2)->default(0.00);
            $table->decimal('food_cost_pct', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['restaurant_id', 'branch_id', 'summary_date', 'menu_item_id', 'item_variant_id'], 'item_summary_unique');
            $table->index(['restaurant_id', 'menu_item_id', 'summary_date'], 'item_sales_rev_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_sales_summaries');
        Schema::dropIfExists('daily_inventory_summaries');
        Schema::dropIfExists('daily_sales_summaries');
        Schema::dropIfExists('shift_staff');
        
        Schema::table('waste_records', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::table('cash_drawers', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::table('customer_sessions', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
        });

        Schema::dropIfExists('shifts');
    }
};

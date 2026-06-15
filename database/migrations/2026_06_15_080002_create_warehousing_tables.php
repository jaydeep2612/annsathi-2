<?php

declare(strict_types=1);

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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->decimal('quantity', 12, 4)->default(0.0000);
            $table->timestamps();

            $table->unique(['warehouse_id', 'grocery_item_id']);
        });

        Schema::create('warehouse_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('grocery_item_id')->constrained('grocery_items')->onDelete('cascade');
            $table->decimal('quantity', 12, 4);
            $table->string('transfer_type'); // e.g., 'dispatch', 'adjustment', 'receipt'
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_movements');
        Schema::dropIfExists('warehouse_stock');
        Schema::dropIfExists('warehouses');
    }
};

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
        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->decimal('rate', 5, 2); // e.g. 5.00 for 5%
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivot table linking tax groups to tax rates
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_group_id')->constrained('tax_groups')->onDelete('cascade');
            $table->foreignId('tax_rate_id')->constrained('tax_rates')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['tax_group_id', 'tax_rate_id']);
        });

        // Add tax_group_id to menu_items table
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('tax_group_id')->nullable()->constrained('tax_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['tax_group_id']);
            $table->dropColumn('tax_group_id');
        });

        Schema::dropIfExists('tax_rules');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_groups');
    }
};

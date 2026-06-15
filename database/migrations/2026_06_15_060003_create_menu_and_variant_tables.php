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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('image_path')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active', 'sort_order']);
        });

        Schema::create('master_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->default(0.00);
            $table->enum('type', ['veg', 'non_veg', 'egg', 'beverage', 'dessert'])->default('veg');
            $table->enum('item_nature', ['premade', 'readymade', 'made_to_order'])->default('made_to_order');
            $table->json('allergens')->nullable();
            $table->smallInteger('prep_time_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('master_menu_item_id')->nullable()->constrained('master_menu_items')->onDelete('set null');
            $table->unsignedBigInteger('kitchen_station_id')->nullable(); // Set after kitchen stations table
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('image_path')->nullable();
            $table->enum('type', ['veg', 'non_veg', 'egg', 'beverage', 'dessert'])->default('veg');
            $table->enum('item_nature', ['premade', 'readymade', 'made_to_order'])->default('made_to_order');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->smallInteger('prep_time_minutes')->nullable();
            $table->json('allergens')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['restaurant_id', 'category_id', 'is_available']);
        });

        Schema::create('branch_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->boolean('is_available')->default(true);
            $table->decimal('override_price', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'menu_item_id']);
        });

        Schema::create('branch_categories', function (Blueprint $table) {
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->primary(['branch_id', 'category_id']);
        });

        Schema::create('item_variant_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->string('name', 80);
            $table->boolean('is_required')->default(true);
            $table->tinyInteger('min_select')->default(1);
            $table->tinyInteger('max_select')->default(1);
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['menu_item_id']);
        });

        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_group_id')->constrained('item_variant_groups')->onDelete('cascade');
            $table->foreignId('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->string('label', 80);
            $table->decimal('price_modifier', 8, 2)->default(0.00);
            $table->enum('price_type', ['add', 'subtract', 'fixed'])->default('add');
            $table->decimal('quantity_value', 8, 3)->nullable();
            $table->string('quantity_unit', 20)->nullable();
            $table->boolean('affects_inventory')->default(true);
            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['menu_item_id', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_variants');
        Schema::dropIfExists('item_variant_groups');
        Schema::dropIfExists('branch_categories');
        Schema::dropIfExists('branch_menu_items');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('master_menu_items');
        Schema::dropIfExists('categories');
    }
};

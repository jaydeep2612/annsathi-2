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
        // 1. Settings Engine Tables
        Schema::create('setting_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['restaurant_id', 'key']);
        });

        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['branch_id', 'key']);
        });

        // 2. SaaS Billing & Feature Gates Tables
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->string('plan_key'); // 'trial', 'basic', 'pro', 'enterprise'
            $table->foreignId('feature_flag_id')->constrained('feature_flags')->onDelete('cascade');
            $table->primary(['plan_key', 'feature_flag_id']);
        });

        Schema::create('restaurant_feature_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('feature_flag_id')->constrained('feature_flags')->onDelete('cascade');
            $table->boolean('is_enabled');
            $table->timestamps();

            $table->unique(['restaurant_id', 'feature_flag_id'], 'rest_feat_over_unique');
        });

        Schema::create('usage_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('metric_key'); // e.g. 'monthly_orders_count'
            $table->integer('usage_count')->default(0);
            $table->timestamp('reset_at')->nullable();
            $table->timestamps();
        });

        Schema::create('usage_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->date('snapshot_date');
            $table->integer('branches_count')->default(0);
            $table->integer('users_count')->default(0);
            $table->integer('orders_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_snapshots');
        Schema::dropIfExists('usage_metrics');
        Schema::dropIfExists('restaurant_feature_overrides');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('branch_settings');
        Schema::dropIfExists('restaurant_settings');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('setting_groups');
    }
};

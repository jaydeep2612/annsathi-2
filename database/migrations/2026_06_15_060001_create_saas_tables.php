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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('upi_id')->nullable();
            $table->enum('subscription_plan', ['trial', 'basic', 'pro', 'enterprise'])->default('trial');
            $table->json('features')->nullable();
            $table->json('settings')->nullable();
            $table->smallInteger('user_limits')->default(5);
            $table->smallInteger('table_limits')->default(10);
            $table->smallInteger('rooms_limit')->default(0);
            $table->smallInteger('max_branches')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Set after users table
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('upi_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('total_served')->default(0);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });

        // Add foreign key constraint back to restaurants
        Schema::table('restaurants', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('user_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'branch_id']);
        });

        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('device_type', ['web', 'mobile', 'tablet'])->default('web');
            $table->enum('action', ['login', 'logout', 'force_logout']);
            $table->string('session_id', 100)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('user_device_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('session_key', 100)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'revoked_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_device_sessions');
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('user_branches');
        
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('restaurants');
    }
};

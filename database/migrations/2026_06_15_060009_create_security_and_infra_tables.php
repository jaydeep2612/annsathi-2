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
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade');
            $table->string('scope', 50);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('response')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['scope', 'status', 'expires_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event'); // e.g., 'invoice.void', 'inventory.adjusted'
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['restaurant_id', 'event', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('idempotency_keys');
    }
};

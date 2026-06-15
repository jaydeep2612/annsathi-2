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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('entity_type'); // Model being approved (e.g., Refund, InventoryTransaction)
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action'); // e.g., 'refund.create', 'stock.adjust'
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained('restaurants')->onDelete('cascade'); // Null for global platform defaults
            $table->string('event_name'); // e.g., 'order_ready', 'waiter_call'
            $table->string('title');
            $table->text('body');
            $table->json('channels'); // ['in_app', 'email', 'whatsapp']
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['restaurant_id', 'event_name']);
        });

        Schema::create('notification_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'in_app', 'email', 'whatsapp', 'sms'
            $table->string('driver')->default('log'); // log, mail, twilio, gupshup
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('event_name'); // e.g., 'order_ready', 'low_stock'
            $table->json('channels'); // Selected channels by user
            $table->timestamps();

            $table->unique(['user_id', 'event_name']);
        });

        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type'); // Info, alert, warning
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('approval_requests');
    }
};

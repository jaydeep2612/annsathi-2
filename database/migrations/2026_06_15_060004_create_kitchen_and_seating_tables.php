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
        Schema::create('kitchen_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 80);
            $table->string('display_color', 7)->nullable();
            $table->string('printer_ip', 45)->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'is_active']);
        });

        // Add foreign key constraint to menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreign('kitchen_station_id')->references('id')->on('kitchen_stations')->onDelete('set null');
        });

        Schema::create('kitchen_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // FK set in order migration
            $table->foreignId('kitchen_station_id')->nullable()->constrained('kitchen_stations')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            $table->enum('current_status', ['placed', 'preparing', 'ready'])->default('placed');
            $table->foreignId('assigned_chef_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['kitchen_station_id', 'current_status']);
            $table->index(['branch_id', 'current_status', 'priority']);
        });

        Schema::create('order_item_kitchen_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id'); // FK set in order migration
            $table->foreignId('kitchen_station_id')->constrained('kitchen_stations')->onDelete('cascade');
            $table->foreignId('kitchen_queue_id')->constrained('kitchen_queue')->onDelete('cascade');
            $table->enum('status', ['queued', 'preparing', 'ready'])->default('queued');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['kitchen_queue_id', 'status']);
        });

        Schema::create('table_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 100);
            $table->foreignId('merged_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('customer_session_id')->nullable(); // Set after sessions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 50);
            $table->tinyInteger('capacity')->default(4);
            $table->string('qr_token', 100)->unique();
            $table->string('qr_image_path', 255)->nullable();
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning'])->default('available');
            $table->foreignId('table_group_id')->nullable()->constrained('table_groups')->onDelete('set null');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'status']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->string('room_number', 20);
            $table->tinyInteger('floor')->nullable();
            $table->tinyInteger('capacity')->default(2);
            $table->decimal('rate_per_night', 10, 2)->default(0.00);
            $table->string('qr_token', 100)->unique();
            $table->string('qr_image_path', 255)->nullable();
            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('parcel_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->string('qr_token', 100)->unique();
            $table->string('qr_image_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->enum('session_type', ['table', 'room', 'parcel']);
            $table->string('session_token', 100)->unique();
            $table->string('sessionable_type', 100);
            $table->unsignedBigInteger('sessionable_id');
            $table->foreignId('host_session_id')->nullable()->constrained('customer_sessions')->onDelete('set null');
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 15)->nullable();
            $table->tinyInteger('pax_count')->default(1);
            $table->enum('status', ['waiting', 'active', 'bill_requested', 'closed'])->default('waiting');
            $table->boolean('is_primary')->default(true);
            $table->enum('join_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamp('actual_checkout_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('shift_id')->nullable(); // Set in shift migration
            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
            $table->index(['sessionable_type', 'sessionable_id', 'status'], 'sessionable_status_idx');
        });

        // Set customer_session_id foreign key on table_groups
        Schema::table('table_groups', function (Blueprint $table) {
            $table->foreign('customer_session_id')->references('id')->on('customer_sessions')->onDelete('set null');
        });

        Schema::create('table_transfer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('customer_session_id')->constrained('customer_sessions')->onDelete('cascade');
            $table->foreignId('from_table_id')->nullable()->constrained('restaurant_tables')->onDelete('set null');
            $table->foreignId('to_table_id')->nullable()->constrained('restaurant_tables')->onDelete('set null');
            $table->foreignId('transferred_by')->constrained('users')->onDelete('cascade');
            $table->string('reason', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['customer_session_id']);
        });

        Schema::create('waiter_table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('restaurant_table_id')->constrained('restaurant_tables')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'restaurant_table_id', 'is_active'], 'waiter_active_assignment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waiter_table_assignments');
        Schema::dropIfExists('table_transfer_logs');
        
        Schema::table('table_groups', function (Blueprint $table) {
            $table->dropForeign(['customer_session_id']);
        });

        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('parcel_counters');
        Schema::dropIfExists('rooms');
        
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropForeign(['table_group_id']);
        });

        Schema::dropIfExists('restaurant_tables');
        Schema::dropIfExists('table_groups');
        Schema::dropIfExists('order_item_kitchen_status');
        Schema::dropIfExists('kitchen_queue');
        
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['kitchen_station_id']);
        });

        Schema::dropIfExists('kitchen_stations');
    }
};

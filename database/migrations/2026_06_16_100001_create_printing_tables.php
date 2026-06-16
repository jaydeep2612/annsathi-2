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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 100);
            $table->enum('connection_type', ['network', 'usb', 'bluetooth'])->default('network');
            $table->string('ip_address', 45)->nullable();
            $table->integer('port')->default(9100);
            $table->string('mac_address', 48)->nullable();
            $table->string('printer_model', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id']);
        });

        Schema::create('printer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id']);
        });

        Schema::create('printer_group_printers', function (Blueprint $table) {
            $table->foreignId('printer_group_id')->constrained('printer_groups')->onDelete('cascade');
            $table->foreignId('printer_id')->constrained('printers')->onDelete('cascade');
            $table->primary(['printer_group_id', 'printer_id']);
        });

        Schema::create('printer_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('kitchen_station_id')->nullable()->constrained('kitchen_stations')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('printer_group_id')->constrained('printer_groups')->onDelete('cascade');
            $table->enum('route_type', ['kot', 'receipt', 'invoice'])->default('kot');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'route_type']);
        });

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('printer_id')->constrained('printers')->onDelete('cascade');
            $table->string('title', 100);
            $table->longText('content');
            $table->enum('status', ['queued', 'printing', 'printed', 'failed'])->default('queued');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('printer_routes');
        Schema::dropIfExists('printer_group_printers');
        Schema::dropIfExists('printer_groups');
        Schema::dropIfExists('printers');
    }
};

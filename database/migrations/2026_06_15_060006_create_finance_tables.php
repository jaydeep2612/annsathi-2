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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('bill_group_id')->nullable()->constrained('bill_groups')->onDelete('set null');
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->unsignedBigInteger('shift_id')->nullable(); // FK set in shifts migration
            $table->enum('payment_method', ['cash', 'upi', 'card', 'room_charge', 'complimentary', 'other']);
            $table->decimal('amount', 10, 2);
            $table->string('reference_note', 255)->nullable();
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'paid', 'partial', 'refunded', 'waived'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'branch_id', 'status', 'created_at'], 'payments_status_index');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->foreignId('customer_session_id')->nullable()->constrained('customer_sessions')->onDelete('set null');
            $table->unsignedBigInteger('shift_id')->nullable(); // FK set in shifts migration
            $table->string('invoice_number', 50)->unique();
            $table->string('invoice_prefix', 20);
            $table->integer('invoice_sequence');
            $table->date('invoice_date');
            $table->string('gstin', 20)->nullable();
            $table->string('place_of_supply', 100)->nullable();
            $table->string('customer_name', 100)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('extra_charges', 10, 2)->default(0.00);
            $table->string('extra_charges_label', 100)->nullable();
            $table->decimal('grand_total', 10, 2);
            $table->json('items_snapshot');
            $table->unsignedBigInteger('voided_by_credit_note_id')->nullable(); // FK set below
            $table->timestamp('created_at')->nullable();

            $table->index(['restaurant_id', 'invoice_date']);
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->string('credit_note_number', 50)->unique();
            $table->text('reason');
            $table->decimal('amount', 10, 2);
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('items_snapshot')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['restaurant_id', 'created_at']);
        });

        // Set missing foreign keys on invoices/payments now that credit notes exist
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('voided_by_credit_note_id')->references('id')->on('credit_notes')->onDelete('set null');
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('credit_note_id')->nullable()->constrained('credit_notes')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->enum('refund_method', ['cash', 'upi', 'card', 'credit_note']);
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approval_request_id')->nullable()->constrained('approval_requests')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['payment_id']);
        });

        Schema::create('cash_drawers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->unsignedBigInteger('shift_id'); // FK set in shifts migration
            $table->foreignId('opened_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('opening_balance', 10, 2)->default(0.00);
            $table->decimal('closing_balance', 10, 2)->nullable();
            $table->decimal('expected_closing_balance', 10, 2)->nullable();
            $table->decimal('variance', 10, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_drawer_id')->constrained('cash_drawers')->onDelete('cascade');
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->enum('type', ['cash_in', 'cash_out', 'opening', 'closing']);
            $table->decimal('amount', 10, 2);
            $table->string('reason', 255);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->nullable();

            $table->index(['cash_drawer_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('cash_drawers');
        Schema::dropIfExists('refunds');
        
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['voided_by_credit_note_id']);
        });

        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
    }
};

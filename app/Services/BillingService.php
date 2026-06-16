<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\CreditNote;
use App\Models\Refund;
use App\Models\ApprovalRequest;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Exception;

class BillingService
{
    /**
     * Generate a new sequential invoice for an order and its payment.
     */
    public function generateInvoice(Order $order, Payment $payment): Invoice
    {
        return DB::transaction(function () use ($order, $payment) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');
            $restaurant = Restaurant::findOrFail($restaurantId);
            $settings = $restaurant->settings;

            $prefix = $settings['invoice_prefix'] ?? 'INV';

            // Safe sequential number generation
            $lastInvoice = Invoice::where('restaurant_id', $restaurantId)
                ->orderBy('invoice_sequence', 'desc')
                ->lockForUpdate()
                ->first();

            $sequence = $lastInvoice ? $lastInvoice->invoice_sequence + 1 : 1;
            $invoiceNumber = $prefix . '-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Snapshot items
            $itemsSnapshot = $order->orderItems->map(function ($item) {
                return [
                    'item_id' => $item->menu_item_id,
                    'name' => $item->item_name,
                    'variant_id' => $item->selected_variant_id,
                    'variant_label' => $item->item_variant_label,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ];
            })->toArray();

            $invoice = Invoice::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'customer_session_id' => $order->customer_session_id,
                'shift_id' => $order->shift_id,
                'invoice_number' => $invoiceNumber,
                'invoice_prefix' => $prefix,
                'invoice_sequence' => $sequence,
                'invoice_date' => now()->toDateString(),
                'gstin' => $settings['gst_no'] ?? null,
                'customer_name' => $order->customer_name,
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'tax_rate' => $order->tax_rate,
                'tax_amount' => $order->tax_amount,
                'extra_charges' => $order->extra_charges,
                'extra_charges_label' => $order->extra_charges_label,
                'grand_total' => $order->total_amount,
                'items_snapshot' => $itemsSnapshot,
                'created_at' => now(),
            ]);

            app(\App\Domains\Accounting\Services\AccountingService::class)->postInvoice($invoice);

            return $invoice;
        });
    }

    /**
     * Issue a credit note for an invoice, voiding it fully or partially.
     */
    public function issueCreditNote(Invoice $invoice, string $reason, ?float $amount = null, ?int $approvedBy = null): CreditNote
    {
        return DB::transaction(function () use ($invoice, $reason, $amount, $approvedBy) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            $creditNoteAmount = $amount ?? $invoice->grand_total;

            if ($invoice->voided_by_credit_note_id) {
                throw new Exception("Invoice {$invoice->invoice_number} is already voided.");
            }

            // Generate Credit Note Number
            $creditNoteNumber = 'CN-' . $invoice->invoice_number;

            $creditNote = CreditNote::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'credit_note_number' => $creditNoteNumber,
                'reason' => $reason,
                'amount' => $creditNoteAmount,
                'issued_by' => auth()->id() ?: $approvedBy,
                'approved_by' => $approvedBy,
                'items_snapshot' => $invoice->items_snapshot,
                'created_at' => now(),
            ]);

            // Void the invoice (allowed by bypassed ImmutableModel rules)
            $invoice->update([
                'voided_by_credit_note_id' => $creditNote->id
            ]);

            // Update order payment status
            if ($invoice->order) {
                $invoice->order->update([
                    'payment_status' => $creditNoteAmount >= $invoice->grand_total ? 'refunded' : 'partially_paid'
                ]);
            }

            event(new \App\Events\CreditNoteIssued($creditNote));

            return $creditNote;
        });
    }

    /**
     * Process a refund for a payment, enforcing manager approval requests checks.
     */
    public function refundPayment(Payment $payment, float $amount, string $reason, string $refundMethod, ?int $approvalRequestId = null): Refund
    {
        return DB::transaction(function () use ($payment, $amount, $reason, $refundMethod, $approvalRequestId) {
            $restaurantId = app('tenant.restaurant_id');

            // 1. Enforce Approval request check
            if (!$approvalRequestId) {
                throw new Exception("Refund processing requires an approved manager approval reference.");
            }

            $approval = ApprovalRequest::findOrFail($approvalRequestId);
            if ($approval->status !== 'approved') {
                throw new Exception("Refund approval request is {$approval->status}. Only approved requests can be processed.");
            }

            if ($approval->action !== 'refund') {
                throw new Exception("Approval request action is {$approval->action}, expected refund.");
            }

            if ($payment->status !== 'paid' && $payment->status !== 'partial') {
                throw new Exception("Payment cannot be refunded. Current payment status is {$payment->status}.");
            }

            if ($amount > $payment->amount) {
                throw new Exception("Refund amount cannot exceed original payment amount.");
            }

            // 2. Process Credit Note if Invoice exists
            $creditNote = null;
            $invoice = Invoice::where('payment_id', $payment->id)->first();
            if ($invoice) {
                $creditNote = $this->issueCreditNote($invoice, "Refund: " . $reason, $amount, $approval->approved_by);
            }

            // 3. Create Refund record
            $refund = Refund::create([
                'payment_id' => $payment->id,
                'restaurant_id' => $restaurantId,
                'credit_note_id' => $creditNote?->id,
                'amount' => $amount,
                'reason' => $reason,
                'refund_method' => $refundMethod,
                'processed_by' => auth()->id() ?: $approval->approved_by,
                'approval_request_id' => $approval->id,
                'processed_at' => now(),
                'created_at' => now(),
            ]);

            // 4. Update Payment Status (allowed by boot check bypass)
            $newPaymentStatus = $amount >= $payment->amount ? 'refunded' : 'partial';
            $payment->update([
                'status' => $newPaymentStatus
            ]);

            return $refund;
        });
    }
}

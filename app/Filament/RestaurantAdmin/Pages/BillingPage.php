<?php

declare(strict_types=1);

namespace App\Filament\RestaurantAdmin\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Services\BillingService;
use App\Services\OrderService;
use App\Services\SessionService;
use Filament\Notifications\Notification;

class BillingPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'POS Billing Terminal';
    protected static ?string $navigationGroup = 'POS & Orders';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->is_super_admin || auth()->user()->hasPermissionTo('record_payments');
    }

    protected static string $view = 'filament.restaurant-admin.pages.billing-page';

    public $selectedOrderId = null;
    public $paymentMethod = 'cash';
    public $referenceNote = '';
    public $notes = '';
    public $cashReceived = 0.00;
    public $changeAmount = 0.00;
    public $releaseTable = true;

    public function getActiveOrders()
    {
        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        return Order::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->with(['orderItems', 'customerSession.sessionable'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $order = Order::findOrFail($orderId);
        $this->cashReceived = (float) $order->total_amount;
        $this->calculateChange();
    }

    public function updatedCashReceived(): void
    {
        $this->calculateChange();
    }

    public function calculateChange(): void
    {
        if (!$this->selectedOrderId) {
            $this->changeAmount = 0.00;
            return;
        }

        $order = Order::findOrFail($this->selectedOrderId);
        $total = (float) $order->total_amount;
        $received = (float) $this->cashReceived;
        $this->changeAmount = max(0.00, $received - $total);
    }

    public function getSelectedOrderProperty()
    {
        if (!$this->selectedOrderId) {
            return null;
        }

        return Order::with(['orderItems', 'customerSession.sessionable'])->find($this->selectedOrderId);
    }

    public function processPayment(): void
    {
        $this->validate([
            'selectedOrderId' => 'required|integer',
            'paymentMethod' => 'required|in:cash,upi,card,room_charge,complimentary,other',
            'cashReceived' => 'required|numeric|min:0',
        ]);

        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        // Verify active shift exists
        $activeShift = Shift::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            Notification::make()
                ->title('Cannot Process Payment')
                ->body('There is no active shift open. Please open a shift first in the Cash Drawer manager.')
                ->danger()
                ->send();
            return;
        }

        $activeDrawer = CashDrawer::where('shift_id', $activeShift->id)
            ->where('status', 'open')
            ->first();

        if ($this->paymentMethod === 'cash' && !$activeDrawer) {
            Notification::make()
                ->title('Cannot Process Payment')
                ->body('Active cash drawer is missing. Please open a shift and drawer first.')
                ->danger()
                ->send();
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($activeShift, $activeDrawer) {
                $order = Order::findOrFail($this->selectedOrderId);

                // 1. Create Payment
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'restaurant_id' => $order->restaurant_id,
                    'branch_id' => $order->branch_id,
                    'shift_id' => $activeShift->id,
                    'payment_method' => $this->paymentMethod,
                    'amount' => $order->total_amount,
                    'reference_note' => $this->referenceNote,
                    'notes' => $this->notes,
                    'received_by' => auth()->id(),
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // 2. If cash, create CashMovement in drawer
                if ($this->paymentMethod === 'cash') {
                    CashMovement::create([
                        'cash_drawer_id' => $activeDrawer->id,
                        'restaurant_id' => $order->restaurant_id,
                        'type' => 'cash_in',
                        'amount' => $order->total_amount,
                        'reason' => 'Sales Order Receipt #' . $order->id,
                        'recorded_by' => auth()->id(),
                        'created_at' => now(),
                    ]);
                }

                // 3. Complete order
                app(OrderService::class)->completeOrder($order->id);

                // 4. Generate Invoice
                app(BillingService::class)->generateInvoice($order, $payment);

                // 5. Release Table / Session check-out if selected
                if ($this->releaseTable && $order->customer_session_id) {
                    app(SessionService::class)->closeSession($order->customer_session_id, auth()->id());
                }
            });

            Notification::make()
                ->title('Payment Successful')
                ->body('Invoice generated and payment recorded successfully.')
                ->success()
                ->send();

            $this->reset(['selectedOrderId', 'referenceNote', 'notes', 'cashReceived', 'changeAmount']);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Payment Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}

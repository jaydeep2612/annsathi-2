<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\CustomerSession;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Models\ItemVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use InvalidArgumentException;

class OrderService
{
    /**
     * Create a new order with its items, calculating all taxes, discounts, and service charges.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');
            $restaurant = Restaurant::findOrFail($restaurantId);
            $settings = $restaurant->settings;

            // 1. Session Validation
            $customerSessionId = $data['customer_session_id'] ?? null;
            $serviceType = $data['service_type']; // dine_in, room_service, parcel, manual
            $customerName = $data['customer_name'] ?? null;

            if ($customerSessionId) {
                $session = CustomerSession::findOrFail($customerSessionId);
                if ($session->status === 'closed') {
                    throw new Exception("Cannot place order. The customer session is closed.");
                }
                $customerName = $customerName ?: $session->customer_name;
            }

            // 2. Waiter Validation if required by settings
            $assignedWaiterId = $data['assigned_waiter_id'] ?? null;
            if (($settings['require_waiter_assignment'] ?? false) && $serviceType === 'dine_in' && !$assignedWaiterId) {
                throw new InvalidArgumentException("Waiter assignment is required for dine-in orders.");
            }

            // 3. Create the Order shell
            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'customer_session_id' => $customerSessionId,
                'service_type' => $serviceType,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'assigned_waiter_id' => $assignedWaiterId,
                'created_by' => auth()->id() ?: $data['created_by'] ?? null,
                'customer_name' => $customerName,
                'notes' => $data['notes'] ?? null,
                'shift_id' => $data['shift_id'] ?? null,
            ]);

            // 4. Create Order Items and Calculate Subtotal
            $subtotal = 0;
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new InvalidArgumentException("An order must contain at least one item.");
            }

            foreach ($itemsData as $itemData) {
                $menuItem = MenuItem::findOrFail($itemData['menu_item_id']);
                
                // Get price and variant modifier
                $price = $menuItem->base_price;
                $variantId = $itemData['selected_variant_id'] ?? null;
                $variantLabel = null;

                if ($variantId) {
                    $variant = ItemVariant::findOrFail($variantId);
                    if ($variant->menu_item_id !== $menuItem->id) {
                        throw new InvalidArgumentException("Selected variant does not belong to the menu item.");
                    }
                    $variantLabel = $variant->label;
                    $price += $variant->price_modifier; // Assumes price_type 'add'
                }

                $qty = $itemData['quantity'] ?? 1;
                $itemTotal = $price * $qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'item_variant_label' => $variantLabel,
                    'selected_variant_id' => $variantId,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total_price' => $itemTotal,
                    'item_nature' => $menuItem->item_nature,
                    'status' => 'pending',
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $subtotal += $itemTotal;
            }

            // 5. Calculate Discounts
            $discountType = $data['discount_type'] ?? null;
            $discountValue = $data['discount_value'] ?? 0;
            $discountAmount = 0;

            if ($discountType && $discountValue > 0) {
                if ($discountType === 'flat') {
                    $discountAmount = min($subtotal, $discountValue);
                } elseif ($discountType === 'percent') {
                    $discountAmount = ($subtotal * $discountValue) / 100;
                }
            }

            // 6. Calculate Taxes (GST)
            $taxRate = $settings['gst_rate'] ?? 5.0;
            $taxableAmount = max(0, $subtotal - $discountAmount);
            $taxAmount = ($taxableAmount * $taxRate) / 100;

            // 7. Calculate Extra Charges (Service Charge if applicable)
            $extraCharges = 0;
            $extraChargesLabel = null;
            if ($serviceType === 'dine_in') {
                $serviceChargePct = $settings['service_charge_pct'] ?? 5.0;
                if ($serviceChargePct > 0) {
                    $extraChargesLabel = $settings['extra_charge_label'] ?? 'Service Charge';
                    $extraCharges = ($taxableAmount * $serviceChargePct) / 100;
                }
            }

            $totalAmount = $taxableAmount + $taxAmount + $extraCharges;

            // 8. Update Order Totals
            $order->update([
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'extra_charges' => $extraCharges,
                'extra_charges_label' => $extraChargesLabel,
                'total_amount' => $totalAmount,
            ]);

            // 9. Log Status Transition
            OrderStatusLog::create([
                'order_id' => $order->id,
                'changed_by' => auth()->id() ?: $data['created_by'] ?? null,
                'from_status' => 'pending',
                'to_status' => 'pending',
                'notes' => 'Order initialized',
            ]);

            // Dispatch Domain Event (Phase 6 placeholder/direct)
            event(new \App\Events\OrderPlaced($order));

            return $order;
        });
    }

    /**
     * Transition order status to 'confirmed'.
     */
    public function confirmOrder(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'confirmed', function ($order) {
            $order->update(['confirmed_at' => now()]);
            event(new \App\Events\OrderConfirmed($order));
        });
    }

    /**
     * Transition order status to 'preparing'.
     */
    public function startPreparing(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'preparing', function ($order) {
            $order->update(['prepared_at' => now()]);
            
            // Also update all pending order items to preparing
            $order->orderItems()->where('status', 'pending')->update(['status' => 'preparing']);

            event(new \App\Events\OrderPreparing($order));
        });
    }

    /**
     * Transition order status to 'ready'.
     */
    public function markReady(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'ready', function ($order) {
            $order->update(['prepared_at' => $order->prepared_at ?: now()]);
            
            // Update items from preparing/pending to ready
            $order->orderItems()->whereIn('status', ['pending', 'preparing'])->update(['status' => 'ready']);

            event(new \App\Events\OrderReady($order));
        });
    }

    /**
     * Transition order status to 'served'.
     */
    public function serveOrder(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'served', function ($order) {
            $order->update(['served_at' => now()]);
            
            // Update items to served
            $order->orderItems()->whereIn('status', ['pending', 'preparing', 'ready'])->update(['status' => 'served']);

            event(new \App\Events\OrderServed($order));
        });
    }

    /**
     * Transition order status to 'completed'. Typically called when the order is paid.
     */
    public function completeOrder(int $orderId): Order
    {
        return $this->transitionStatus($orderId, 'completed', function ($order) {
            $order->update(['completed_at' => now()]);
            event(new \App\Events\OrderCompleted($order));
        });
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(int $orderId, string $reason, ?int $cancelledBy = null): Order
    {
        return DB::transaction(function () use ($orderId, $reason, $cancelledBy) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if (in_array($order->status, ['completed', 'cancelled'])) {
                throw new Exception("Cannot cancel an order that is already {$order->status}.");
            }

            if ($order->payment_status === 'paid') {
                throw new Exception("Cannot cancel a paid order. Please issue a refund/credit note instead.");
            }

            $fromStatus = $order->status;
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Cancel all items
            $order->orderItems()->update(['status' => 'cancelled']);

            // Log change
            OrderStatusLog::create([
                'order_id' => $order->id,
                'changed_by' => $cancelledBy ?: auth()->id(),
                'from_status' => $fromStatus,
                'to_status' => 'cancelled',
                'notes' => $reason,
            ]);

            event(new \App\Events\OrderCancelled($order, $reason));

            return $order;
        });
    }

    /**
     * Internal status transition helper enforcing validation and logging.
     */
    protected function transitionStatus(int $orderId, string $toStatus, callable $callback): Order
    {
        return DB::transaction(function () use ($orderId, $toStatus, $callback) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $fromStatus = $order->status;

            if ($fromStatus === $toStatus) {
                return $order;
            }

            if ($fromStatus === 'cancelled') {
                throw new Exception("Cannot change status of a cancelled order.");
            }

            if ($fromStatus === 'completed' && $toStatus !== 'cancelled') {
                throw new Exception("Cannot change status of a completed order.");
            }

            // Verify status lifecycle sequence
            $statusSequence = [
                'pending' => 1,
                'confirmed' => 2,
                'preparing' => 3,
                'ready' => 4,
                'served' => 5,
                'completed' => 6
            ];

            $fromSeq = $statusSequence[$fromStatus] ?? 0;
            $toSeq = $statusSequence[$toStatus] ?? 0;

            if ($toSeq > 0 && $fromSeq > 0 && $toSeq < $fromSeq) {
                throw new Exception("Invalid status transition from {$fromStatus} to {$toStatus}.");
            }

            $order->update(['status' => $toStatus]);

            // Execute custom transition logic
            $callback($order);

            // Log change
            OrderStatusLog::create([
                'order_id' => $order->id,
                'changed_by' => auth()->id(),
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'notes' => "Status transitioned to {$toStatus}",
            ]);

            return $order;
        });
    }
}

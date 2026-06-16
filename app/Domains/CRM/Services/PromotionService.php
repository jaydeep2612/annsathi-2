<?php

declare(strict_types=1);

namespace App\Domains\CRM\Services;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\OrderPromotion;
use Illuminate\Support\Facades\DB;
use Exception;

class PromotionService
{
    /**
     * Apply a coupon code to an order.
     */
    public function applyCoupon(Order $order, string $code): Order
    {
        return DB::transaction(function () use ($order, $code) {
            // Find active promotion
            $promo = Promotion::where('restaurant_id', $order->restaurant_id)
                ->where('code', $code)
                ->where('is_active', true)
                ->first();

            if (!$promo) {
                throw new Exception("Coupon code '{$code}' is invalid or expired.");
            }

            // Verify dates
            $today = now()->toDateString();
            if ($promo->start_date && $promo->start_date->toDateString() > $today) {
                throw new Exception("Coupon code '{$code}' is not active yet.");
            }
            if ($promo->end_date && $promo->end_date->toDateString() < $today) {
                throw new Exception("Coupon code '{$code}' has expired.");
            }

            // Verify minimum order amount
            if ($order->subtotal < (float) $promo->min_order_amount) {
                throw new Exception("Minimum order subtotal of INR " . number_format((float) $promo->min_order_amount, 2) . " is required to apply this coupon.");
            }

            // Calculate discount
            $discount = $this->calculateDiscount($order, $promo);

            if ($discount <= 0) {
                throw new Exception("Coupon does not apply to any items in the order or yields zero discount.");
            }

            // Apply discount to order
            $order->update([
                'discount_amount' => $order->discount_amount + $discount,
                'total_amount' => max(0.00, $order->total_amount - $discount),
            ]);

            // Save order promotion link
            OrderPromotion::create([
                'order_id' => $order->id,
                'promotion_id' => $promo->id,
                'discount_amount' => $discount,
            ]);

            return $order;
        });
    }

    /**
     * Calculate discount amount for a promotion without mutating the order.
     */
    public function calculateDiscount(Order $order, Promotion $promo): float
    {
        $discount = 0.00;
        $order->load('orderItems');

        if ($promo->type === 'flat') {
            $discount = (float) $promo->value;
        } elseif ($promo->type === 'percent') {
            $discount = ($order->subtotal * (float) $promo->value / 100.0);
            if ($promo->max_discount_amount) {
                $discount = min($discount, (float) $promo->max_discount_amount);
            }
        } elseif ($promo->type === 'bogo') {
            $buyItemId = $promo->bogo_buy_menu_item_id;
            $getItemId = $promo->bogo_get_menu_item_id;

            $buyItem = $order->orderItems->firstWhere('menu_item_id', $buyItemId);
            $getItem = $order->orderItems->firstWhere('menu_item_id', $getItemId);

            if ($buyItem && $getItem) {
                $qtyBuy = (float) $buyItem->quantity;
                $qtyGet = (float) $getItem->quantity;

                // 1 unit free for each buy unit purchased
                $freeUnits = min($qtyBuy, $qtyGet);
                $unitPrice = (float) ($getItem->unit_price ?? ($getItem->total_price / $qtyGet));

                $discount = $freeUnits * $unitPrice;
            }
        }

        // Capped at remaining order total
        return min($discount, (float) $order->total_amount);
    }
}

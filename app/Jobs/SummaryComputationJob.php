<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\GroceryItem;
use App\Models\InventoryTransaction;
use App\Models\DailySalesSummary;
use App\Models\DailyInventorySummary;
use App\Models\ItemSalesSummary;
use App\Models\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Log;

class SummaryComputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Running SummaryComputationJob...");

        $date = Carbon::yesterday(); // Aggregate data for yesterday
        $restaurants = Restaurant::where('is_active', true)->get();

        foreach ($restaurants as $restaurant) {
            app()->bind('tenant.restaurant_id', fn() => $restaurant->id);
            $branches = Branch::where('restaurant_id', $restaurant->id)->get();

            foreach ($branches as $branch) {
                app()->bind('tenant.branch_id', fn() => $branch->id);

                try {
                    $this->computeSalesSummary($restaurant->id, $branch->id, $date);
                    $this->computeInventorySummary($restaurant->id, $branch->id, $date);
                    $this->computeItemSalesSummary($restaurant->id, $branch->id, $date);
                } catch (\Exception $e) {
                    Log::error("Summary aggregation failed for Restaurant {$restaurant->id}, Branch {$branch->id}: " . $e->getMessage());
                }
            }
        }
    }

    protected function computeSalesSummary(int $restaurantId, int $branchId, Carbon $date): void
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $date->toDateString())
            ->get();

        $completed = $orders->where('status', 'completed');
        $cancelled = $orders->where('status', 'cancelled');

        if ($orders->count() === 0) return;

        $gross = $completed->sum(fn($o) => $o->subtotal + $o->extra_charges);
        $discount = $completed->sum('discount_amount');
        $tax = $completed->sum('tax_amount');
        $extra = $completed->sum('extra_charges');
        $net = $completed->sum('total_amount');

        $payments = Payment::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereDate('created_at', $date->toDateString())
            ->get();

        $cash = $payments->where('payment_method', 'cash')->sum('amount');
        $upi = $payments->where('payment_method', 'upi')->sum('amount');
        $card = $payments->where('payment_method', 'card')->sum('amount');
        $comp = $payments->where('payment_method', 'complimentary')->sum('amount');

        // Peak hour
        $peakHour = null;
        $hours = [];
        foreach ($completed as $o) {
            if ($o->completed_at) {
                $h = Carbon::parse($o->completed_at)->hour;
                $hours[$h] = ($hours[$h] ?? 0) + 1;
            }
        }
        if (!empty($hours)) {
            arsort($hours);
            $peakHour = key($hours);
        }

        DailySalesSummary::updateOrCreate([
            'restaurant_id' => $restaurantId,
            'branch_id' => $branchId,
            'summary_date' => $date->toDateString(),
            'shift_id' => null,
        ], [
            'total_orders' => $orders->count(),
            'completed_orders' => $completed->count(),
            'cancelled_orders' => $cancelled->count(),
            'gross_revenue' => $gross,
            'discount_total' => $discount,
            'tax_total' => $tax,
            'extra_charges_total' => $extra,
            'net_revenue' => $net,
            'cash_collected' => $cash,
            'upi_collected' => $upi,
            'card_collected' => $card,
            'complimentary_total' => $comp,
            'avg_order_value' => $completed->count() > 0 ? $net / $completed->count() : 0,
            'peak_hour' => $peakHour,
            'dine_in_orders' => $completed->where('service_type', 'dine_in')->count(),
            'room_service_orders' => $completed->where('service_type', 'room_service')->count(),
            'parcel_orders' => $completed->where('service_type', 'parcel')->count(),
            'manual_orders' => $completed->where('service_type', 'manual')->count(),
            'computed_at' => now(),
        ]);
    }

    protected function computeInventorySummary(int $restaurantId, int $branchId, Carbon $date): void
    {
        $items = GroceryItem::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->get();

        foreach ($items as $item) {
            $txs = InventoryTransaction::where('grocery_item_id', $item->id)
                ->whereDate('created_at', $date->toDateString())
                ->get();

            $prev = DailyInventorySummary::where('grocery_item_id', $item->id)
                ->where('summary_date', $date->copy()->subDay()->toDateString())
                ->first();

            $opening = $prev ? $prev->closing_stock : 0.0000;
            $additions = $txs->whereIn('type', ['addition', 'purchase_receipt'])->sum('quantity');
            $consumed = abs($txs->where('type', 'order_fulfillment')->sum('quantity'));
            $waste = abs($txs->where('type', 'waste')->sum('quantity'));
            $adj = $txs->where('type', 'adjustment')->sum('quantity');

            $closing = $opening + $additions - $consumed - $waste + $adj;

            $cost = $item->cost_per_unit ?? 0;

            DailyInventorySummary::updateOrCreate([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'summary_date' => $date->toDateString(),
                'grocery_item_id' => $item->id,
            ], [
                'opening_stock' => $opening,
                'additions' => $additions,
                'consumed' => $consumed,
                'waste' => $waste,
                'adjustments' => $adj,
                'closing_stock' => $closing,
                'waste_cost' => $waste * $cost,
                'consumption_cost' => $consumed * $cost,
                'computed_at' => now(),
            ]);
        }
    }

    protected function computeItemSalesSummary(int $restaurantId, int $branchId, Carbon $date): void
    {
        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereDate('created_at', $date->toDateString())
            ->pluck('id');

        $orderItems = OrderItem::whereIn('order_id', $orders)->get();

        $grouped = $orderItems->groupBy(function ($item) {
            return $item->menu_item_id . '-' . ($item->selected_variant_id ?: 'null');
        });

        foreach ($grouped as $key => $items) {
            $first = $items->first();
            $menuItemId = $first->menu_item_id;
            $variantId = $first->selected_variant_id;

            $qty = $items->sum('quantity');
            $gross = $items->sum('total_price');

            // Resolve Food Cost
            $cost = 0;
            $recipes = Recipe::where('menu_item_id', $menuItemId)
                ->when($variantId, fn($q) => $q->where('item_variant_id', $variantId))
                ->where('is_current', true)
                ->get();

            foreach ($recipes as $recipe) {
                $groceryItem = GroceryItem::find($recipe->grocery_item_id);
                $cost += ($recipe->quantity_required * $qty) * ($groceryItem->cost_per_unit ?? 0);
            }

            ItemSalesSummary::updateOrCreate([
                'restaurant_id' => $restaurantId,
                'branch_id' => $branchId,
                'summary_date' => $date->toDateString(),
                'menu_item_id' => $menuItemId,
                'item_variant_id' => $variantId,
            ], [
                'quantity_sold' => $qty,
                'gross_revenue' => $gross,
                'food_cost' => $cost,
                'gross_profit' => $gross - $cost,
                'food_cost_pct' => $gross > 0 ? ($cost / $gross) * 100 : 0,
            ]);
        }
    }
}

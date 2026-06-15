<?php

namespace App\Services;

use App\Models\ItemSalesSummary;
use App\Models\DailySalesSummary;
use App\Models\WasteRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AnalyticsService
{
    /**
     * Build the Menu Engineering Matrix (Stars, Plowhorses, Puzzles, Dogs) for a date range.
     */
    public function getMenuEngineeringMatrix(string $startDate, string $endDate): array
    {
        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        // Fetch sales summaries grouped by menu item and variant
        $summaries = ItemSalesSummary::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereBetween('summary_date', [$startDate, $endDate])
            ->select(
                'menu_item_id',
                'item_variant_id',
                DB::raw('SUM(quantity_sold) as total_qty'),
                DB::raw('SUM(gross_revenue) as total_rev'),
                DB::raw('SUM(food_cost) as total_cost'),
                DB::raw('SUM(gross_profit) as total_profit')
            )
            ->groupBy('menu_item_id', 'item_variant_id')
            ->get();

        if ($summaries->isEmpty()) {
            return [
                'stars' => [],
                'plowhorses' => [],
                'puzzles' => [],
                'dogs' => [],
                'averages' => ['qty' => 0, 'profit' => 0]
            ];
        }

        // Calculate averages for threshold classification
        $avgQty = $summaries->avg('total_qty');
        $avgProfit = $summaries->avg('total_profit');

        $matrix = [
            'stars' => [],       // High Popularity, High Profit
            'plowhorses' => [],  // High Popularity, Low Profit
            'puzzles' => [],     // Low Popularity, High Profit
            'dogs' => [],        // Low Popularity, Low Profit
            'averages' => [
                'qty' => round($avgQty, 2),
                'profit' => round($avgProfit, 2)
            ]
        ];

        foreach ($summaries as $item) {
            $item->load(['menuItem', 'itemVariant']);
            
            $itemData = [
                'id' => $item->menu_item_id,
                'name' => $item->menuItem?->name ?? 'Unknown',
                'variant_label' => $item->itemVariant?->label ?? 'Base',
                'quantity_sold' => (int)$item->total_qty,
                'gross_revenue' => (float)$item->total_rev,
                'food_cost' => (float)$item->total_cost,
                'gross_profit' => (float)$item->total_profit,
                'food_cost_pct' => $item->total_rev > 0 ? round(($item->total_cost / $item->total_rev) * 100, 2) : 0,
            ];

            $isPopular = $item->total_qty >= $avgQty;
            $isProfitable = $item->total_profit >= $avgProfit;

            if ($isPopular && $isProfitable) {
                $matrix['stars'][] = $itemData;
            } elseif ($isPopular && !$isProfitable) {
                $matrix['plowhorses'][] = $itemData;
            } elseif (!$isPopular && $isProfitable) {
                $matrix['puzzles'][] = $itemData;
            } else {
                $matrix['dogs'][] = $itemData;
            }
        }

        return $matrix;
    }

    /**
     * Compute food costing, waste impact, and net margin analyses.
     */
    public function getFoodCostAndWasteReport(string $startDate, string $endDate): array
    {
        $restaurantId = app('tenant.restaurant_id');
        $branchId = app('tenant.branch_id');

        // Revenue summaries
        $salesSummaries = DailySalesSummary::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereBetween('summary_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(gross_revenue) as gross_rev'),
                DB::raw('SUM(discount_total) as discount_total'),
                DB::raw('SUM(tax_total) as tax_total'),
                DB::raw('SUM(net_revenue) as net_rev')
            )
            ->first();

        // Food cost from item sales summaries
        $totalFoodCost = ItemSalesSummary::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereBetween('summary_date', [$startDate, $endDate])
            ->sum('food_cost');

        // Waste totals
        $totalWasteCost = WasteRecord::where('restaurant_id', $restaurantId)
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ])
            ->sum('total_cost');

        $grossRev = (float)($salesSummaries->gross_rev ?? 0);
        $netRev = (float)($salesSummaries->net_rev ?? 0);
        $foodCost = (float)$totalFoodCost;
        $wasteCost = (float)$totalWasteCost;

        $foodCostPct = $grossRev > 0 ? round(($foodCost / $grossRev) * 100, 2) : 0;
        $wasteCostPct = $foodCost > 0 ? round(($wasteCost / $foodCost) * 100, 2) : 0;
        $netMarginPct = $netRev > 0 ? round((($netRev - $foodCost - $wasteCost) / $netRev) * 100, 2) : 0;

        return [
            'gross_revenue' => $grossRev,
            'net_revenue' => $netRev,
            'total_food_cost' => $foodCost,
            'total_waste_cost' => $wasteCost,
            'food_cost_percentage' => $foodCostPct,
            'waste_to_food_cost_percentage' => $wasteCostPct,
            'estimated_net_profit_margin_percentage' => $netMarginPct,
            'computed_at' => now()->toDateTimeString(),
        ];
    }
}

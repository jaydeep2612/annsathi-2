<?php

declare(strict_types=1);

namespace App\Domains\Printing\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Printer;
use App\Models\PrinterGroup;
use App\Models\PrinterRoute;
use App\Models\PrintJob;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrinterRoutingService
{
    /**
     * Route KOTs for an order to the corresponding kitchen printers.
     */
    public function routeOrderKOT(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            $order->load(['orderItems.menuItem', 'waiter']);

            // Group order items by target printer group
            $groupPrintJobs = [];

            foreach ($order->orderItems as $item) {
                $menuItem = $item->menuItem;
                if (!$menuItem) {
                    continue;
                }

                // 1. Specific route matching station or category
                $route = PrinterRoute::where('restaurant_id', $restaurantId)
                    ->where('branch_id', $branchId)
                    ->where('route_type', 'kot')
                    ->where('is_active', true)
                    ->where(function ($q) use ($menuItem) {
                        $q->where('kitchen_station_id', $menuItem->kitchen_station_id)
                          ->orWhere('category_id', $menuItem->category_id);
                    })
                    ->orderByRaw('kitchen_station_id DESC') // station takes precedence
                    ->first();

                // 2. Fallback route where both station and category are NULL
                if (!$route) {
                    $route = PrinterRoute::where('restaurant_id', $restaurantId)
                        ->where('branch_id', $branchId)
                        ->where('route_type', 'kot')
                        ->where('is_active', true)
                        ->whereNull('kitchen_station_id')
                        ->whereNull('category_id')
                        ->first();
                }

                if ($route) {
                    $groupPrintJobs[$route->printer_group_id][] = $item;
                }
            }

            // Create print jobs for each group
            foreach ($groupPrintJobs as $groupId => $items) {
                $group = PrinterGroup::with('printers')->find($groupId);
                if (!$group) {
                    continue;
                }

                $content = $this->generateKOTContent($order, $items);

                foreach ($group->printers as $printer) {
                    if (!$printer->is_active) {
                        continue;
                    }

                    PrintJob::create([
                        'restaurant_id' => $restaurantId,
                        'branch_id' => $branchId,
                        'printer_id' => $printer->id,
                        'title' => "KOT - Order #{$order->id}",
                        'content' => $content,
                        'status' => 'queued',
                    ]);
                }
            }
        });
    }

    /**
     * Route receipt for an order to the billing printers.
     */
    public function routeOrderReceipt(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $restaurantId = app('tenant.restaurant_id');
            $branchId = app('tenant.branch_id');

            $order->load(['orderItems.menuItem']);

            // Find billing receipt route
            $route = PrinterRoute::where('restaurant_id', $restaurantId)
                ->where('branch_id', $branchId)
                ->where('route_type', 'receipt')
                ->where('is_active', true)
                ->first();

            if (!$route) {
                // Fallback receipt route (central/default group)
                $route = PrinterRoute::where('restaurant_id', $restaurantId)
                    ->where('branch_id', $branchId)
                    ->where('route_type', 'receipt')
                    ->where('is_active', true)
                    ->whereNull('kitchen_station_id')
                    ->whereNull('category_id')
                    ->first();
            }

            if (!$route) {
                return;
            }

            $group = PrinterGroup::with('printers')->find($route->printer_group_id);
            if (!$group) {
                return;
            }

            $content = $this->generateReceiptContent($order);

            foreach ($group->printers as $printer) {
                if (!$printer->is_active) {
                    continue;
                }

                PrintJob::create([
                    'restaurant_id' => $restaurantId,
                    'branch_id' => $branchId,
                    'printer_id' => $printer->id,
                    'title' => "Receipt - Order #{$order->id}",
                    'content' => $content,
                    'status' => 'queued',
                ]);
            }
        });
    }

    /**
     * Generate textual KOT payload.
     */
    protected function generateKOTContent(Order $order, array $items): string
    {
        $timeStr = Carbon::now()->format('Y-m-d H:i:s');
        $waiter = $order->waiter ? $order->waiter->name : 'N/A';
        $tableName = 'N/A';
        
        if ($order->customerSession && $order->customerSession->sessionable) {
            $tableName = $order->customerSession->sessionable->name;
        }

        $kot = "========================================\n";
        $kot .= "            KITCHEN ORDER TICKET        \n";
        $kot .= "========================================\n";
        $kot .= "Order ID: #ORD-{$order->id}\n";
        $kot .= "Table   : {$tableName}\n";
        $kot .= "Date    : {$timeStr}\n";
        $kot .= "Waiter  : {$waiter}\n";
        $kot .= "----------------------------------------\n";
        $kot .= sprintf("%-5s %-25s %-8s\n", "Qty", "Item Name", "Variant");
        $kot .= "----------------------------------------\n";

        foreach ($items as $item) {
            $variant = $item->item_variant_label ?: '-';
            $kot .= sprintf("%-5d %-25s %-8s\n", $item->quantity, substr($item->item_name, 0, 24), $variant);
            if ($item->notes) {
                $kot .= "  * NOTE: {$item->notes}\n";
            }
        }
        $kot .= "========================================\n";

        return $kot;
    }

    /**
     * Generate textual Receipt payload.
     */
    protected function generateReceiptContent(Order $order): string
    {
        $timeStr = Carbon::now()->format('Y-m-d H:i:s');
        $tableName = 'N/A';
        
        if ($order->customerSession && $order->customerSession->sessionable) {
            $tableName = $order->customerSession->sessionable->name;
        }

        $receipt = "========================================\n";
        $receipt .= "             RECEIPT / INVOICE          \n";
        $receipt .= "========================================\n";
        $receipt .= "Order ID: #ORD-{$order->id}\n";
        $receipt .= "Table   : {$tableName}\n";
        $receipt .= "Date    : {$timeStr}\n";
        $receipt .= "----------------------------------------\n";
        $receipt .= sprintf("%-20s %-5s %-12s\n", "Item Name", "Qty", "Price");
        $receipt .= "----------------------------------------\n";

        foreach ($order->orderItems as $item) {
            $name = $item->item_name;
            if ($item->item_variant_label) {
                $name .= " (" . $item->item_variant_label . ")";
            }
            $receipt .= sprintf("%-20s %-5d %-12.2f\n", substr($name, 0, 19), $item->quantity, $item->total_price);
        }

        $receipt .= "----------------------------------------\n";
        $receipt .= sprintf("%-27s %12.2f\n", "Subtotal:", $order->subtotal);
        if ($order->discount_amount > 0) {
            $receipt .= sprintf("%-27s %12.2f\n", "Discount:", -$order->discount_amount);
        }
        $receipt .= sprintf("%-27s %12.2f\n", "GST / Tax:", $order->tax_amount);
        if ($order->extra_charges > 0) {
            $label = $order->extra_charges_label ?: 'Service Charge:';
            $receipt .= sprintf("%-27s %12.2f\n", $label, $order->extra_charges);
        }
        $receipt .= "----------------------------------------\n";
        $receipt .= sprintf("%-27s %12.2f\n", "GRAND TOTAL:", $order->total_amount);
        $receipt .= "========================================\n";
        $receipt .= "         Thank You! Visit Again         \n";
        $receipt .= "========================================\n";

        return $receipt;
    }
}

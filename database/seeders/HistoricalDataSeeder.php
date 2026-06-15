<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Shift;
use App\Models\CashDrawer;
use App\Models\CashMovement;
use App\Models\RestaurantTable;
use App\Models\Room;
use App\Models\ParcelCounter;
use App\Models\CustomerSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\GroceryItem;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use App\Models\WasteRecord;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\DailySalesSummary;
use App\Models\DailyInventorySummary;
use App\Models\ItemSalesSummary;
use App\Models\MenuItem;
use App\Models\ItemVariant;
use App\Models\Recipe;
use App\Models\Supplier;
use App\Models\MeasurementUnit;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HistoricalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch Tenant Context
        $restaurant = Restaurant::where('slug', 'demo-restaurant')->first();
        if (!$restaurant) {
            $this->command->error('Demo restaurant not found. Please run RestaurantSeeder first.');
            return;
        }
        $branch = Branch::where('restaurant_id', $restaurant->id)->first();
        if (!$branch) {
            $this->command->error('Colaba Branch not found. Please run RestaurantSeeder first.');
            return;
        }

        // Bind Tenant parameters to the application container so observers can resolve them automatically
        app()->bind('tenant.restaurant_id', fn() => $restaurant->id);
        app()->bind('tenant.branch_id', fn() => $branch->id);

        // 2. Fetch Users
        $manager = User::where('email', 'manager@annsathi.com')->first();
        $chef = User::where('email', 'chef@annsathi.com')->first();
        $waiter = User::where('email', 'waiter@annsathi.com')->first();

        if (!$manager || !$chef || !$waiter) {
            $this->command->error('Core users (manager, chef, waiter) are missing. Please run RestaurantSeeder first.');
            return;
        }

        // 3. Create Tables, Rooms, and Takeaway counters if they don't exist
        $tables = [];
        for ($t = 1; $t <= 8; $t++) {
            $tables[] = RestaurantTable::firstOrCreate([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'name' => "Table $t",
            ], [
                'capacity' => $t <= 4 ? 2 : 4,
                'qr_token' => "table-$t-qr-" . Str::random(8),
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        $rooms = [];
        for ($r = 101; $r <= 104; $r++) {
            $rooms[] = Room::firstOrCreate([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'room_number' => (string) $r,
            ], [
                'name' => "Room $r",
                'capacity' => 2,
                'rate_per_night' => 2000.00,
                'qr_token' => "room-$r-qr-" . Str::random(8),
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        $parcelCounter = ParcelCounter::firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Main Takeaway Counter',
        ], [
            'qr_token' => 'takeaway-counter-qr-' . Str::random(8),
            'is_active' => true,
        ]);

        // 4. Fetch Menu items and variants
        $menuItems = MenuItem::where('restaurant_id', $restaurant->id)->get();
        $biryani = MenuItem::where('slug', 'mutton-dum-biryani')->first();
        $cappuccino = MenuItem::where('slug', 'classic-cappuccino')->first();
        $iceCream = MenuItem::where('slug', 'vanilla-ice-cream-cup')->first();
        $water = MenuItem::where('slug', 'mineral-water-bottle')->first();

        $biryaniHalf = ItemVariant::where('menu_item_id', $biryani->id)->where('label', 'Half Portion')->first();
        $biryaniFull = ItemVariant::where('menu_item_id', $biryani->id)->where('label', 'Full Portion')->first();

        // 5. Fetch Grocery items & reset stock to simulate starting from Day 1
        $groceryItems = GroceryItem::where('restaurant_id', $restaurant->id)->get();
        $supplier = Supplier::where('restaurant_id', $restaurant->id)->first();

        foreach ($groceryItems as $item) {
            $item->update(['current_stock' => 0.0000]);
        }

        // Define replenishment function
        $replenishStock = function (Carbon $date, $itemsToRestock, $performedBy) use ($restaurant, $branch, $supplier) {
            $poNumber = 'PO-' . $date->format('Ymd') . '-' . Str::upper(Str::random(4));

            $po = PurchaseOrder::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'po_number' => $poNumber,
                'status' => 'received',
                'ordered_by' => $performedBy->id,
                'expected_delivery_date' => $date->toDateString(),
                'notes' => 'Automatic restock seeder PO',
                'total_amount' => 0.00,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $grn = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'received_by' => $performedBy->id,
                'receipt_date' => $date->toDateString(),
                'notes' => 'Received items for ' . $poNumber,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $totalPoAmount = 0;

            foreach ($itemsToRestock as $itemId => $qty) {
                $item = GroceryItem::find($itemId);
                $cost = $item->cost_per_unit ?? 0.10;
                $totalCost = $qty * $cost;

                $poItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'grocery_item_id' => $item->id,
                    'measurement_unit_id' => $item->measurement_unit_id,
                    'ordered_quantity' => $qty,
                    'received_quantity' => $qty,
                    'unit_price' => $cost,
                    'total_price' => $totalCost,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $grnItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $grn->id,
                    'purchase_order_item_id' => $poItem->id,
                    'grocery_item_id' => $item->id,
                    'quantity_received' => $qty,
                    'unit_cost' => $cost,
                    'total_cost' => $totalCost,
                    'batch_number' => 'BATCH-' . $date->format('Ymd') . '-' . $item->sku,
                    'expiry_date' => $date->copy()->addDays(90)->toDateString(),
                    'quality_status' => 'accepted',
                    'notes' => 'Automatic seed check-in',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $batch = InventoryBatch::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'grocery_item_id' => $item->id,
                    'batch_number' => $grnItem->batch_number,
                    'supplier_id' => $supplier->id,
                    'initial_quantity' => $qty,
                    'current_quantity' => $qty,
                    'unit_cost' => $cost,
                    'received_date' => $date->toDateString(),
                    'expiry_date' => $grnItem->expiry_date,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $oldStock = $item->current_stock;
                $newStock = $oldStock + $qty;
                $item->update(['current_stock' => $newStock]);

                InventoryTransaction::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'grocery_item_id' => $item->id,
                    'inventory_batch_id' => $batch->id,
                    'type' => 'purchase_receipt',
                    'quantity' => $qty,
                    'balance_after' => $newStock,
                    'unit_cost' => $cost,
                    'total_cost' => $totalCost,
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $grn->id,
                    'performed_by' => $performedBy->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $totalPoAmount += $totalCost;
            }

            $po->update(['total_amount' => $totalPoAmount]);
        };

        // Auto-restock check logic
        $stockCheck = function (Carbon $date, $performedBy) use ($groceryItems, $replenishStock) {
            $itemsToRestock = [];
            foreach ($groceryItems as $item) {
                $freshItem = GroceryItem::find($item->id);
                if ($freshItem->current_stock <= $freshItem->low_stock_threshold) {
                    $itemsToRestock[$freshItem->id] = $freshItem->reorder_quantity ?? 20000.0000;
                }
            }
            if (!empty($itemsToRestock)) {
                $replenishStock($date, $itemsToRestock, $performedBy);
            }
        };

        // Waste generation logic
        $generateWaste = function (Carbon $wasteTime, Shift $shift) use ($restaurant, $branch, $chef, $groceryItems) {
            $wastedItem = $groceryItems->random();
            $qty = rand(5, 20) * 100; // 500g to 2000g
            if ($wastedItem->sku === 'VAN-ICE-05') {
                $qty = rand(1, 3);
            }

            $cost = $wastedItem->cost_per_unit ?? 0.10;
            $totalCost = $qty * $cost;

            $reasons = ['expired', 'spoilage', 'kitchen_mistake', 'overproduction'];
            $reason = $reasons[array_rand($reasons)];

            $waste = WasteRecord::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'grocery_item_id' => $wastedItem->id,
                'measurement_unit_id' => $wastedItem->measurement_unit_id,
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total_cost' => $totalCost,
                'reason' => $reason,
                'reason_notes' => 'Simulated waste from seeder',
                'recorded_by' => $chef->id,
                'shift_id' => $shift->id,
                'created_at' => $wasteTime,
            ]);

            $oldStock = $wastedItem->current_stock;
            $newStock = max(0, $oldStock - $qty);
            $wastedItem->update(['current_stock' => $newStock]);

            InventoryTransaction::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'grocery_item_id' => $wastedItem->id,
                'inventory_batch_id' => null,
                'type' => 'waste',
                'quantity' => -$qty,
                'balance_after' => $newStock,
                'unit_cost' => $cost,
                'total_cost' => $totalCost,
                'reference_type' => WasteRecord::class,
                'reference_id' => $waste->id,
                'performed_by' => $chef->id,
                'created_at' => $wasteTime,
                'updated_at' => $wasteTime,
            ]);
        };

        // 6. Define Order generation logic
        $generateOrder = function (
            Carbon $orderTime,
            Shift $shift,
            CashDrawer $drawer,
            &$invoiceSequence,
            &$cashCollected,
            &$upiCollected,
            &$cardCollected
        ) use (
            $restaurant,
            $branch,
            $manager,
            $waiter,
            $chef,
            $tables,
            $rooms,
            $parcelCounter,
            $menuItems,
            $biryani,
            $biryaniHalf,
            $biryaniFull,
            $cappuccino,
            $iceCream,
            $water
        ) {
            $randService = rand(1, 100);
            if ($randService <= 60) {
                $serviceType = 'dine_in';
            } elseif ($randService <= 85) {
                $serviceType = 'parcel';
            } elseif ($randService <= 95) {
                $serviceType = 'room_service';
            } else {
                $serviceType = 'manual';
            }

            $customerSession = null;
            $customerName = 'Customer ' . rand(100, 999);

            if ($serviceType === 'dine_in') {
                $table = $tables[array_rand($tables)];
                $customerSession = CustomerSession::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'session_type' => 'table',
                    'session_token' => 'token-' . Str::random(12),
                    'sessionable_type' => RestaurantTable::class,
                    'sessionable_id' => $table->id,
                    'customer_name' => $customerName,
                    'pax_count' => rand(1, 4),
                    'status' => 'closed',
                    'shift_id' => $shift->id,
                    'check_in_at' => $orderTime->copy()->subMinutes(rand(30, 90)),
                    'closed_at' => $orderTime,
                    'closed_by' => $manager->id,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);
            } elseif ($serviceType === 'room_service') {
                $room = $rooms[array_rand($rooms)];
                $customerSession = CustomerSession::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'session_type' => 'room',
                    'session_token' => 'token-' . Str::random(12),
                    'sessionable_type' => Room::class,
                    'sessionable_id' => $room->id,
                    'customer_name' => $customerName,
                    'pax_count' => rand(1, 2),
                    'status' => 'closed',
                    'shift_id' => $shift->id,
                    'check_in_at' => $orderTime->copy()->subMinutes(rand(30, 120)),
                    'closed_at' => $orderTime,
                    'closed_by' => $manager->id,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);
            } elseif ($serviceType === 'parcel') {
                $customerSession = CustomerSession::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'session_type' => 'parcel',
                    'session_token' => 'token-' . Str::random(12),
                    'sessionable_type' => ParcelCounter::class,
                    'sessionable_id' => $parcelCounter->id,
                    'customer_name' => $customerName,
                    'pax_count' => 1,
                    'status' => 'closed',
                    'shift_id' => $shift->id,
                    'check_in_at' => $orderTime->copy()->subMinutes(rand(5, 20)),
                    'closed_at' => $orderTime,
                    'closed_by' => $manager->id,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);
            }

            $isCancelled = (rand(1, 100) <= 5);
            $orderStatus = $isCancelled ? 'cancelled' : 'completed';
            $paymentStatus = $isCancelled ? 'unpaid' : 'paid';

            $order = Order::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'customer_session_id' => $customerSession?->id,
                'service_type' => $serviceType,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'assigned_waiter_id' => $waiter->id,
                'created_by' => $waiter->id,
                'customer_name' => $customerName,
                'shift_id' => $shift->id,
                'created_at' => $orderTime,
                'updated_at' => $orderTime,
            ]);

            $subtotal = 0;
            $itemsCount = rand(1, 3);
            $orderItemsSnapshot = [];

            for ($i = 0; $i < $itemsCount; $i++) {
                $mItem = $menuItems->random();
                $variant = null;
                $variantLabel = null;
                $price = $mItem->base_price;

                if ($mItem->id === $biryani->id) {
                    $variant = rand(0, 1) === 0 ? $biryaniHalf : $biryaniFull;
                    $variantLabel = $variant->label;
                    $price += $variant->price_modifier;
                }

                $qty = rand(1, 2);
                $totalPrice = $price * $qty;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $mItem->id,
                    'item_name' => $mItem->name,
                    'item_variant_label' => $variantLabel,
                    'selected_variant_id' => $variant?->id,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'total_price' => $totalPrice,
                    'item_nature' => $mItem->item_nature,
                    'status' => $isCancelled ? 'cancelled' : 'served',
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);

                $orderItemsSnapshot[] = [
                    'item_id' => $mItem->id,
                    'name' => $mItem->name,
                    'variant_id' => $variant?->id,
                    'variant_label' => $variantLabel,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $totalPrice,
                ];

                $subtotal += $totalPrice;

                // Ingredient deduction on completed order items
                if (!$isCancelled) {
                    $recipes = Recipe::where('menu_item_id', $mItem->id)
                        ->when($variant, fn($q) => $q->where('item_variant_id', $variant->id))
                        ->where('is_current', true)
                        ->get();

                    foreach ($recipes as $recipe) {
                        $groceryItem = GroceryItem::find($recipe->grocery_item_id);
                        $qtyNeeded = $recipe->quantity_required * $qty;

                        $oldStock = $groceryItem->current_stock;
                        $newStock = max(0, $oldStock - $qtyNeeded);
                        $groceryItem->update(['current_stock' => $newStock]);

                        InventoryTransaction::create([
                            'restaurant_id' => $restaurant->id,
                            'branch_id' => $branch->id,
                            'grocery_item_id' => $groceryItem->id,
                            'inventory_batch_id' => null,
                            'type' => 'order_fulfillment',
                            'quantity' => -$qtyNeeded,
                            'balance_after' => $newStock,
                            'unit_cost' => $groceryItem->cost_per_unit,
                            'total_cost' => $qtyNeeded * ($groceryItem->cost_per_unit ?? 0),
                            'reference_type' => OrderItem::class,
                            'reference_id' => $orderItem->id,
                            'performed_by' => $chef->id,
                            'created_at' => $orderTime,
                            'updated_at' => $orderTime,
                        ]);
                    }
                }
            }

            // Discount calculations
            $discountType = null;
            $discountValue = 0;
            $discountAmount = 0;
            if (rand(1, 100) <= 12) {
                if (rand(0, 1) === 0) {
                    $discountType = 'flat';
                    $discountValue = rand(1, 3) * 50;
                    $discountAmount = min($subtotal, $discountValue);
                } else {
                    $discountType = 'percent';
                    $discountValue = 10;
                    $discountAmount = ($subtotal * 10) / 100;
                }
            }

            $taxRate = 5.00;
            $taxableAmount = max(0, $subtotal - $discountAmount);
            $taxAmount = ($taxableAmount * $taxRate) / 100;

            $extraCharges = 0;
            $extraChargesLabel = null;
            if ($serviceType === 'dine_in') {
                $extraChargesLabel = 'Service Charge';
                $extraCharges = ($taxableAmount * 5.00) / 100;
            }

            $totalAmount = $taxableAmount + $taxAmount + $extraCharges;

            $order->update([
                'status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'extra_charges' => $extraCharges,
                'extra_charges_label' => $extraChargesLabel,
                'total_amount' => $totalAmount,
                'confirmed_at' => $orderTime->copy()->addMinutes(1),
                'prepared_at' => $isCancelled ? null : $orderTime->copy()->addMinutes(12),
                'served_at' => $isCancelled ? null : $orderTime->copy()->addMinutes(15),
                'completed_at' => $isCancelled ? null : $orderTime->copy()->addMinutes(25),
                'cancelled_at' => $isCancelled ? $orderTime->copy()->addMinutes(5) : null,
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'changed_by' => $manager->id,
                'from_status' => 'pending',
                'to_status' => $orderStatus,
                'notes' => $isCancelled ? 'Order aborted' : 'Order completed',
                'created_at' => $orderTime,
            ]);

            if (!$isCancelled) {
                $payMethodRand = rand(1, 100);
                if ($payMethodRand <= 35) {
                    $payMethod = 'cash';
                    $cashCollected += $totalAmount;
                } elseif ($payMethodRand <= 85) {
                    $payMethod = 'upi';
                    $upiCollected += $totalAmount;
                } else {
                    $payMethod = 'card';
                    $cardCollected += $totalAmount;
                }

                $payment = Payment::create([
                    'order_id' => $order->id,
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'shift_id' => $shift->id,
                    'payment_method' => $payMethod,
                    'amount' => $totalAmount,
                    'received_by' => $manager->id,
                    'status' => 'paid',
                    'paid_at' => $orderTime->copy()->addMinutes(24),
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);

                $invoiceNum = 'GF-' . $orderTime->format('Ymd') . '-' . str_pad($invoiceSequence, 4, '0', STR_PAD_LEFT);

                Invoice::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'customer_session_id' => $customerSession?->id,
                    'shift_id' => $shift->id,
                    'invoice_number' => $invoiceNum,
                    'invoice_prefix' => 'GF',
                    'invoice_sequence' => $invoiceSequence,
                    'invoice_date' => $orderTime->toDateString(),
                    'gstin' => '27AAACT1234A1Z1',
                    'place_of_supply' => 'Maharashtra',
                    'customer_name' => $customerName,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'extra_charges' => $extraCharges,
                    'extra_charges_label' => $extraChargesLabel,
                    'grand_total' => $totalAmount,
                    'items_snapshot' => $orderItemsSnapshot,
                    'created_at' => $orderTime->copy()->addMinutes(25),
                ]);

                $invoiceSequence++;

                if ($payMethod === 'cash') {
                    CashMovement::create([
                        'cash_drawer_id' => $drawer->id,
                        'restaurant_id' => $restaurant->id,
                        'type' => 'cash_in',
                        'amount' => $totalAmount,
                        'reason' => "Payment for Invoice $invoiceNum",
                        'reference_id' => $payment->id,
                        'reference_type' => Payment::class,
                        'recorded_by' => $manager->id,
                        'created_at' => $orderTime->copy()->addMinutes(25),
                    ]);
                }
            }

            return $order;
        };

        // 7. Define Shift summary logic
        $computeShiftSummary = function (Carbon $date, Shift $shift) use ($restaurant, $branch) {
            $orders = Order::where('shift_id', $shift->id)->get();
            $completedOrders = $orders->where('status', 'completed');
            $cancelledOrders = $orders->where('status', 'cancelled');

            $totalOrdersCount = $orders->count();
            $completedOrdersCount = $completedOrders->count();
            $cancelledOrdersCount = $cancelledOrders->count();

            if ($totalOrdersCount === 0) return;

            $grossRevenue = $completedOrders->sum(fn($o) => $o->subtotal + $o->extra_charges);
            $discountTotal = $completedOrders->sum('discount_amount');
            $taxTotal = $completedOrders->sum('tax_amount');
            $extraChargesTotal = $completedOrders->sum('extra_charges');
            $netRevenue = $completedOrders->sum('total_amount');

            $payments = Payment::where('shift_id', $shift->id)->where('status', 'paid')->get();
            $cashCollected = $payments->where('payment_method', 'cash')->sum('amount');
            $upiCollected = $payments->where('payment_method', 'upi')->sum('amount');
            $cardCollected = $payments->where('payment_method', 'card')->sum('amount');
            $complimentaryTotal = $payments->where('payment_method', 'complimentary')->sum('amount');

            $avgOrderValue = $completedOrdersCount > 0 ? $netRevenue / $completedOrdersCount : 0;

            $peakHour = null;
            $hourCounts = [];
            foreach ($completedOrders as $order) {
                if ($order->completed_at) {
                    $hour = Carbon::parse($order->completed_at)->hour;
                    $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
                }
            }
            if (!empty($hourCounts)) {
                arsort($hourCounts);
                $peakHour = key($hourCounts);
            }

            $dineInOrders = $completedOrders->where('service_type', 'dine_in')->count();
            $roomServiceOrders = $completedOrders->where('service_type', 'room_service')->count();
            $parcelOrders = $completedOrders->where('service_type', 'parcel')->count();
            $manualOrders = $completedOrders->where('service_type', 'manual')->count();

            DailySalesSummary::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'summary_date' => $date->toDateString(),
                'shift_id' => $shift->id,
                'total_orders' => $totalOrdersCount,
                'completed_orders' => $completedOrdersCount,
                'cancelled_orders' => $cancelledOrdersCount,
                'gross_revenue' => $grossRevenue,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'extra_charges_total' => $extraChargesTotal,
                'net_revenue' => $netRevenue,
                'cash_collected' => $cashCollected,
                'upi_collected' => $upiCollected,
                'card_collected' => $cardCollected,
                'complimentary_total' => $complimentaryTotal,
                'avg_order_value' => $avgOrderValue,
                'peak_hour' => $peakHour,
                'dine_in_orders' => $dineInOrders,
                'room_service_orders' => $roomServiceOrders,
                'parcel_orders' => $parcelOrders,
                'manual_orders' => $manualOrders,
                'computed_at' => Carbon::now(),
            ]);
        };

        // Daily summary logic
        $computeDailySalesSummary = function (Carbon $date) use ($restaurant, $branch) {
            $orders = Order::whereDate('created_at', $date->toDateString())
                ->where('restaurant_id', $restaurant->id)
                ->get();

            $completedOrders = $orders->where('status', 'completed');
            $cancelledOrders = $orders->where('status', 'cancelled');

            $totalOrdersCount = $orders->count();
            $completedOrdersCount = $completedOrders->count();
            $cancelledOrdersCount = $cancelledOrders->count();

            if ($totalOrdersCount === 0) return;

            $grossRevenue = $completedOrders->sum(fn($o) => $o->subtotal + $o->extra_charges);
            $discountTotal = $completedOrders->sum('discount_amount');
            $taxTotal = $completedOrders->sum('tax_amount');
            $extraChargesTotal = $completedOrders->sum('extra_charges');
            $netRevenue = $completedOrders->sum('total_amount');

            $payments = Payment::whereDate('created_at', $date->toDateString())
                ->where('restaurant_id', $restaurant->id)
                ->where('status', 'paid')
                ->get();

            $cashCollected = $payments->where('payment_method', 'cash')->sum('amount');
            $upiCollected = $payments->where('payment_method', 'upi')->sum('amount');
            $cardCollected = $payments->where('payment_method', 'card')->sum('amount');
            $complimentaryTotal = $payments->where('payment_method', 'complimentary')->sum('amount');

            $avgOrderValue = $completedOrdersCount > 0 ? $netRevenue / $completedOrdersCount : 0;

            $peakHour = null;
            $hourCounts = [];
            foreach ($completedOrders as $order) {
                if ($order->completed_at) {
                    $hour = Carbon::parse($order->completed_at)->hour;
                    $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
                }
            }
            if (!empty($hourCounts)) {
                arsort($hourCounts);
                $peakHour = key($hourCounts);
            }

            $dineInOrders = $completedOrders->where('service_type', 'dine_in')->count();
            $roomServiceOrders = $completedOrders->where('service_type', 'room_service')->count();
            $parcelOrders = $completedOrders->where('service_type', 'parcel')->count();
            $manualOrders = $completedOrders->where('service_type', 'manual')->count();

            DailySalesSummary::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'summary_date' => $date->toDateString(),
                'shift_id' => null,
                'total_orders' => $totalOrdersCount,
                'completed_orders' => $completedOrdersCount,
                'cancelled_orders' => $cancelledOrdersCount,
                'gross_revenue' => $grossRevenue,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'extra_charges_total' => $extraChargesTotal,
                'net_revenue' => $netRevenue,
                'cash_collected' => $cashCollected,
                'upi_collected' => $upiCollected,
                'card_collected' => $cardCollected,
                'complimentary_total' => $complimentaryTotal,
                'avg_order_value' => $avgOrderValue,
                'peak_hour' => $peakHour,
                'dine_in_orders' => $dineInOrders,
                'room_service_orders' => $roomServiceOrders,
                'parcel_orders' => $parcelOrders,
                'manual_orders' => $manualOrders,
                'computed_at' => Carbon::now(),
            ]);
        };

        // Inventory summary logic
        $computeDailyInventorySummary = function (Carbon $date) use ($restaurant, $branch, $groceryItems) {
            foreach ($groceryItems as $item) {
                $transactions = InventoryTransaction::where('grocery_item_id', $item->id)
                    ->whereDate('created_at', $date->toDateString())
                    ->get();

                $prevSummary = DailyInventorySummary::where('grocery_item_id', $item->id)
                    ->where('summary_date', $date->copy()->subDay()->toDateString())
                    ->first();

                $openingStock = $prevSummary ? $prevSummary->closing_stock : 0.0000;

                $additions = $transactions->whereIn('type', ['addition', 'purchase_receipt'])->sum('quantity');
                $consumed = abs($transactions->where('type', 'order_fulfillment')->sum('quantity'));
                $waste = abs($transactions->where('type', 'waste')->sum('quantity'));
                $adjustments = $transactions->where('type', 'adjustment')->sum('quantity');

                $closingStock = $openingStock + $additions - $consumed - $waste + $adjustments;

                $cost = $item->cost_per_unit ?? 0.10;
                $wasteCost = $waste * $cost;
                $consumptionCost = $consumed * $cost;

                DailyInventorySummary::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'summary_date' => $date->toDateString(),
                    'grocery_item_id' => $item->id,
                    'opening_stock' => $openingStock,
                    'additions' => $additions,
                    'consumed' => $consumed,
                    'waste' => $waste,
                    'adjustments' => $adjustments,
                    'closing_stock' => $closingStock,
                    'waste_cost' => $wasteCost,
                    'consumption_cost' => $consumptionCost,
                    'computed_at' => Carbon::now(),
                ]);
            }
        };

        // Item Sales summary logic
        $computeItemSalesSummary = function (Carbon $date) use ($restaurant, $branch) {
            $completedOrdersIds = Order::whereDate('created_at', $date->toDateString())
                ->where('restaurant_id', $restaurant->id)
                ->where('status', 'completed')
                ->pluck('id');

            $orderItems = OrderItem::whereIn('order_id', $completedOrdersIds)->get();

            $grouped = $orderItems->groupBy(function ($item) {
                return $item->menu_item_id . '-' . ($item->selected_variant_id ?: 'null');
            });

            foreach ($grouped as $key => $items) {
                $first = $items->first();
                $menuItemId = $first->menu_item_id;
                $variantId = $first->selected_variant_id;

                $quantitySold = $items->sum('quantity');
                $grossRevenue = $items->sum('total_price');

                $foodCost = 0;
                $recipes = Recipe::where('menu_item_id', $menuItemId)
                    ->when($variantId, fn($q) => $q->where('item_variant_id', $variantId))
                    ->where('is_current', true)
                    ->get();

                foreach ($recipes as $recipe) {
                    $groceryItem = GroceryItem::find($recipe->grocery_item_id);
                    $qtyNeeded = $recipe->quantity_required * $quantitySold;
                    $foodCost += $qtyNeeded * ($groceryItem->cost_per_unit ?? 0);
                }

                $grossProfit = $grossRevenue - $foodCost;
                $foodCostPct = $grossRevenue > 0 ? ($foodCost / $grossRevenue) * 100 : 0;

                ItemSalesSummary::create([
                    'restaurant_id' => $restaurant->id,
                    'branch_id' => $branch->id,
                    'summary_date' => $date->toDateString(),
                    'menu_item_id' => $menuItemId,
                    'item_variant_id' => $variantId,
                    'quantity_sold' => $quantitySold,
                    'gross_revenue' => $grossRevenue,
                    'food_cost' => $foodCost,
                    'gross_profit' => $grossProfit,
                    'food_cost_pct' => $foodCostPct,
                ]);
            }
        };

        // 8. Execute 30-day simulation loop
        $invoiceSequence = 1;
        $startDate = Carbon::now()->subDays(30)->startOfDay();

        // Let's seed initial stock values on start of Day 1
        $riceItem = $groceryItems->where('sku', 'BAS-RIC-01')->first();
        $muttonItem = $groceryItems->where('sku', 'MUT-RAW-02')->first();
        $coffeeBeansItem = $groceryItems->where('sku', 'ARA-COF-03')->first();
        $milkItem = $groceryItems->where('sku', 'MIL-FCR-04')->first();
        $iceCreamBaseItem = $groceryItems->where('sku', 'VAN-ICE-05')->first();

        $initialRestock = [
            $riceItem->id => 120000.0000,      // 120 kg
            $muttonItem->id => 60000.0000,      // 60 kg
            $coffeeBeansItem->id => 25000.0000,  // 25 kg
            $milkItem->id => 80000.0000,        // 80 L
            $iceCreamBaseItem->id => 300.0000,   // 300 units
        ];

        $replenishStock($startDate->copy()->setTime(7, 0, 0), $initialRestock, $manager);

        for ($day = 0; $day < 30; $day++) {
            $currentDate = $startDate->copy()->addDays($day);

            // Trigger stock checks and replenishment at start of day
            if ($day > 0) {
                $stockCheck($currentDate->copy()->setTime(8, 0, 0), $manager);
            }

            // Morning Shift
            $morningStart = $currentDate->copy()->setTime(9, 0, 0);
            $morningEnd = $currentDate->copy()->setTime(16, 0, 0);

            $morningShift = Shift::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'name' => 'Morning Shift',
                'shift_type' => 'morning',
                'started_by' => $manager->id,
                'ended_by' => $manager->id,
                'start_time' => $morningStart,
                'end_time' => $morningEnd,
                'status' => 'closed',
            ]);

            $morningDrawer = CashDrawer::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'shift_id' => $morningShift->id,
                'opened_by' => $manager->id,
                'closed_by' => $manager->id,
                'opening_balance' => 5000.00,
                'expected_closing_balance' => 5000.00,
                'closing_balance' => 5000.00,
                'variance' => 0.00,
                'status' => 'closed',
                'opened_at' => $morningStart,
                'closed_at' => $morningEnd,
            ]);

            CashMovement::create([
                'cash_drawer_id' => $morningDrawer->id,
                'restaurant_id' => $restaurant->id,
                'type' => 'opening',
                'amount' => 5000.00,
                'reason' => 'Shift cash opening balance',
                'recorded_by' => $manager->id,
                'created_at' => $morningStart,
            ]);

            $morningCash = 0;
            $morningUpi = 0;
            $morningCard = 0;
            $morningOrdersNum = rand(10, 18);

            for ($o = 0; $o < $morningOrdersNum; $o++) {
                $oTime = $morningStart->copy()->addMinutes(rand(10, 400));
                $generateOrder($oTime, $morningShift, $morningDrawer, $invoiceSequence, $morningCash, $morningUpi, $morningCard);
            }

            // Close morning drawer
            $expectedMorningCashTotal = 5000.00 + $morningCash;
            $morningVariance = (rand(1, 15) === 15) ? (rand(-2, 2) * 5) : 0;
            $actualMorningCashTotal = $expectedMorningCashTotal + $morningVariance;

            $morningDrawer->update([
                'expected_closing_balance' => $expectedMorningCashTotal,
                'closing_balance' => $actualMorningCashTotal,
                'variance' => $morningVariance,
            ]);

            CashMovement::create([
                'cash_drawer_id' => $morningDrawer->id,
                'restaurant_id' => $restaurant->id,
                'type' => 'closing',
                'amount' => $actualMorningCashTotal,
                'reason' => 'Shift cash closing balance',
                'recorded_by' => $manager->id,
                'created_at' => $morningEnd,
            ]);

            // Evening Shift
            $eveningStart = $currentDate->copy()->setTime(16, 0, 0);
            $eveningEnd = $currentDate->copy()->setTime(23, 0, 0);

            $eveningShift = Shift::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'name' => 'Evening Shift',
                'shift_type' => 'evening',
                'started_by' => $manager->id,
                'ended_by' => $manager->id,
                'start_time' => $eveningStart,
                'end_time' => $eveningEnd,
                'status' => 'closed',
            ]);

            $eveningDrawer = CashDrawer::create([
                'restaurant_id' => $restaurant->id,
                'branch_id' => $branch->id,
                'shift_id' => $eveningShift->id,
                'opened_by' => $manager->id,
                'closed_by' => $manager->id,
                'opening_balance' => $actualMorningCashTotal,
                'expected_closing_balance' => $actualMorningCashTotal,
                'closing_balance' => $actualMorningCashTotal,
                'variance' => 0.00,
                'status' => 'closed',
                'opened_at' => $eveningStart,
                'closed_at' => $eveningEnd,
            ]);

            CashMovement::create([
                'cash_drawer_id' => $eveningDrawer->id,
                'restaurant_id' => $restaurant->id,
                'type' => 'opening',
                'amount' => $actualMorningCashTotal,
                'reason' => 'Shift cash opening balance',
                'recorded_by' => $manager->id,
                'created_at' => $eveningStart,
            ]);

            $eveningCash = 0;
            $eveningUpi = 0;
            $eveningCard = 0;
            $eveningOrdersNum = rand(15, 26);

            for ($o = 0; $o < $eveningOrdersNum; $o++) {
                $oTime = $eveningStart->copy()->addMinutes(rand(10, 400));
                $generateOrder($oTime, $eveningShift, $eveningDrawer, $invoiceSequence, $eveningCash, $eveningUpi, $eveningCard);
            }

            // Close evening drawer
            $expectedEveningCashTotal = $actualMorningCashTotal + $eveningCash;
            $eveningVariance = (rand(1, 15) === 15) ? (rand(-2, 2) * 5) : 0;
            $actualEveningCashTotal = $expectedEveningCashTotal + $eveningVariance;

            $eveningDrawer->update([
                'expected_closing_balance' => $expectedEveningCashTotal,
                'closing_balance' => $actualEveningCashTotal,
                'variance' => $eveningVariance,
            ]);

            CashMovement::create([
                'cash_drawer_id' => $eveningDrawer->id,
                'restaurant_id' => $restaurant->id,
                'type' => 'closing',
                'amount' => $actualEveningCashTotal,
                'reason' => 'Shift cash closing balance',
                'recorded_by' => $manager->id,
                'created_at' => $eveningEnd,
            ]);

            // Waste recording (every 3 days)
            if ($day % 3 === 0) {
                $generateWaste($currentDate->copy()->setTime(22, 30, 0), $eveningShift);
            }

            // Compute Daily Sales/Inventory/Item Summaries
            $computeShiftSummary($currentDate, $morningShift);
            $computeShiftSummary($currentDate, $eveningShift);
            $computeDailySalesSummary($currentDate);
            $computeDailyInventorySummary($currentDate);
            $computeItemSalesSummary($currentDate);
        }

        $this->command->info('Historical seeder completed successfully.');
    }
}

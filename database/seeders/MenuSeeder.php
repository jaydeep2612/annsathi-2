<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Branch;
use App\Models\KitchenStation;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\ItemVariantGroup;
use App\Models\ItemVariant;
use App\Models\MeasurementUnit;
use App\Models\GroceryItem;
use App\Models\Recipe;
use App\Models\Supplier;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $restaurant = Restaurant::where('slug', 'demo-restaurant')->first();
        if (!$restaurant) return;
        $branch = Branch::where('restaurant_id', $restaurant->id)->first();

        // 1. Create Kitchen Stations
        $mainKitchen = KitchenStation::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Main Kitchen',
            'display_color' => '#E53E3E',
            'is_active' => true,
        ]);

        $barStation = KitchenStation::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Bar Counter',
            'display_color' => '#3182CE',
            'is_active' => true,
        ]);

        $dessertStation = KitchenStation::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'name' => 'Dessert Station',
            'display_color' => '#D69E2E',
            'is_active' => true,
        ]);

        // 2. Create Categories
        $desiCategory = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Desi Delights',
            'slug' => 'desi-delights',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $beverageCategory = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Beverages',
            'slug' => 'beverages',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $dessertCategory = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Sweet Indulgences',
            'slug' => 'sweet-indulgences',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // 3. Create Measurement Units
        $kg = MeasurementUnit::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kilogram',
            'short_name' => 'kg',
            'conversion_factor' => 1.0000,
        ]);

        $gm = MeasurementUnit::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Gram',
            'short_name' => 'gm',
            'base_unit' => 'kg',
            'conversion_factor' => 0.0010,
        ]);

        $liter = MeasurementUnit::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Liter',
            'short_name' => 'L',
            'conversion_factor' => 1.0000,
        ]);

        $ml = MeasurementUnit::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Milliliter',
            'short_name' => 'ml',
            'base_unit' => 'L',
            'conversion_factor' => 0.0010,
        ]);

        $pcs = MeasurementUnit::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Pieces',
            'short_name' => 'pcs',
            'conversion_factor' => 1.0000,
        ]);

        // 4. Create Supplier
        $supplier = Supplier::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Global Foods Inc',
            'contact_person' => 'Mr. Sharma',
            'phone' => '+919988776655',
            'email' => 'sharma@globalfoods.com',
            'is_active' => true,
        ]);

        // 5. Create Raw Grocery Items
        $rice = GroceryItem::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'measurement_unit_id' => $gm->id,
            'supplier_id' => $supplier->id,
            'name' => 'Basmati Rice',
            'sku' => 'BAS-RIC-01',
            'current_stock' => 50000.0000, // 50 kg
            'low_stock_threshold' => 10000.0000, // 10 kg
            'reorder_quantity' => 50000.0000,
            'cost_per_unit' => 0.10, // ₹0.10 per gram (₹100/kg)
            'avg_cost_per_unit' => 0.10,
        ]);

        $mutton = GroceryItem::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'measurement_unit_id' => $gm->id,
            'supplier_id' => $supplier->id,
            'name' => 'Raw Mutton',
            'sku' => 'MUT-RAW-02',
            'current_stock' => 20000.0000, // 20 kg
            'low_stock_threshold' => 5000.0000, // 5 kg
            'reorder_quantity' => 20000.0000,
            'cost_per_unit' => 0.60, // ₹0.60 per gram (₹600/kg)
            'avg_cost_per_unit' => 0.60,
        ]);

        $coffeeBeans = GroceryItem::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'measurement_unit_id' => $gm->id,
            'supplier_id' => $supplier->id,
            'name' => 'Arabica Coffee Beans',
            'sku' => 'ARA-COF-03',
            'current_stock' => 10000.0000, // 10 kg
            'low_stock_threshold' => 2000.0000,
            'reorder_quantity' => 10000.0000,
            'cost_per_unit' => 1.20, // ₹1.20 per gram (₹1200/kg)
            'avg_cost_per_unit' => 1.20,
        ]);

        $milk = GroceryItem::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'measurement_unit_id' => $ml->id,
            'supplier_id' => $supplier->id,
            'name' => 'Full Cream Milk',
            'sku' => 'MIL-FCR-04',
            'current_stock' => 30000.0000, // 30 Liters
            'low_stock_threshold' => 5000.0000,
            'reorder_quantity' => 20000.0000,
            'cost_per_unit' => 0.06, // ₹0.06 per ml (₹60/Liter)
            'avg_cost_per_unit' => 0.06,
        ]);

        $iceCreamBase = GroceryItem::create([
            'restaurant_id' => $restaurant->id,
            'branch_id' => $branch->id,
            'measurement_unit_id' => $pcs->id,
            'supplier_id' => $supplier->id,
            'name' => 'Premade Vanilla Cups',
            'sku' => 'VAN-ICE-05',
            'current_stock' => 100.0000, // 100 cups
            'low_stock_threshold' => 20.0000,
            'reorder_quantity' => 50.0000,
            'cost_per_unit' => 25.00, // ₹25 per cup
            'avg_cost_per_unit' => 25.00,
        ]);

        // 6. Create Menu Items
        // A. Mutton Biryani (Made to order, Main Kitchen)
        $biryani = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $desiCategory->id,
            'kitchen_station_id' => $mainKitchen->id,
            'name' => 'Mutton Dum Biryani',
            'slug' => 'mutton-dum-biryani',
            'description' => 'Fragrant Basmati rice cooked with succulent pieces of mutton and spices.',
            'base_price' => 350.00,
            'type' => 'non_veg',
            'item_nature' => 'made_to_order',
            'prep_time_minutes' => 20,
            'is_available' => true,
        ]);

        // Variant Group for Biryani Portion
        $portionGroup = ItemVariantGroup::create([
            'restaurant_id' => $restaurant->id,
            'menu_item_id' => $biryani->id,
            'name' => 'Portion Size',
            'is_required' => true,
            'min_select' => 1,
            'max_select' => 1,
        ]);

        $halfBiryani = ItemVariant::create([
            'variant_group_id' => $portionGroup->id,
            'menu_item_id' => $biryani->id,
            'label' => 'Half Portion',
            'price_modifier' => 0.00, // base price
            'price_type' => 'add',
            'quantity_value' => 0.500,
            'quantity_unit' => 'plate',
            'affects_inventory' => true,
        ]);

        $fullBiryani = ItemVariant::create([
            'variant_group_id' => $portionGroup->id,
            'menu_item_id' => $biryani->id,
            'label' => 'Full Portion',
            'price_modifier' => 150.00, // base price + 150 = 500
            'price_type' => 'add',
            'quantity_value' => 1.000,
            'quantity_unit' => 'plate',
            'affects_inventory' => true,
        ]);

        // Biryani Recipe
        // Half portion uses: 120g rice, 150g mutton
        Recipe::create([
            'menu_item_id' => $biryani->id,
            'item_variant_id' => $halfBiryani->id,
            'grocery_item_id' => $rice->id,
            'measurement_unit_id' => $gm->id,
            'quantity_required' => 120.0000,
            'is_current' => true,
        ]);
        Recipe::create([
            'menu_item_id' => $biryani->id,
            'item_variant_id' => $halfBiryani->id,
            'grocery_item_id' => $mutton->id,
            'measurement_unit_id' => $gm->id,
            'quantity_required' => 150.0000,
            'is_current' => true,
        ]);

        // Full portion uses: 240g rice, 300g mutton
        Recipe::create([
            'menu_item_id' => $biryani->id,
            'item_variant_id' => $fullBiryani->id,
            'grocery_item_id' => $rice->id,
            'measurement_unit_id' => $gm->id,
            'quantity_required' => 240.0000,
            'is_current' => true,
        ]);
        Recipe::create([
            'menu_item_id' => $biryani->id,
            'item_variant_id' => $fullBiryani->id,
            'grocery_item_id' => $mutton->id,
            'measurement_unit_id' => $gm->id,
            'quantity_required' => 300.0000,
            'is_current' => true,
        ]);

        // B. Cappuccino Coffee (Made to order, Bar Counter)
        $cappuccino = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $beverageCategory->id,
            'kitchen_station_id' => $barStation->id,
            'name' => 'Classic Cappuccino',
            'slug' => 'classic-cappuccino',
            'description' => 'Espresso shot with steamed milk foam and cocoa powder dust.',
            'base_price' => 120.00,
            'type' => 'veg',
            'item_nature' => 'made_to_order',
            'prep_time_minutes' => 5,
            'is_available' => true,
        ]);

        // Recipe for Cappuccino: 15g Coffee Beans, 150ml Milk
        Recipe::create([
            'menu_item_id' => $cappuccino->id,
            'grocery_item_id' => $coffeeBeans->id,
            'measurement_unit_id' => $gm->id,
            'quantity_required' => 15.0000,
            'is_current' => true,
        ]);
        Recipe::create([
            'menu_item_id' => $cappuccino->id,
            'grocery_item_id' => $milk->id,
            'measurement_unit_id' => $ml->id,
            'quantity_required' => 150.0000,
            'is_current' => true,
        ]);

        // C. Vanilla Ice Cream (Premade, Dessert Station)
        $vanillaIceCream = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $dessertCategory->id,
            'kitchen_station_id' => $dessertStation->id,
            'name' => 'Vanilla Ice Cream Cup',
            'slug' => 'vanilla-ice-cream-cup',
            'description' => 'Smooth vanilla bean ice cream served in a cup.',
            'base_price' => 80.00,
            'type' => 'veg',
            'item_nature' => 'premade',
            'prep_time_minutes' => 2,
            'is_available' => true,
        ]);

        // Recipe for Vanilla: 1 Premade Vanilla Cup
        Recipe::create([
            'menu_item_id' => $vanillaIceCream->id,
            'grocery_item_id' => $iceCreamBase->id,
            'measurement_unit_id' => $pcs->id,
            'quantity_required' => 1.0000,
            'is_current' => true,
        ]);

        // D. Bottled Water (Readymade - no kitchen station routing, no recipes)
        $water = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $beverageCategory->id,
            'kitchen_station_id' => null, // routes to default / none needed
            'name' => 'Mineral Water Bottle',
            'slug' => 'mineral-water-bottle',
            'description' => 'Chilled 1 Liter mineral water bottle.',
            'base_price' => 40.00,
            'type' => 'beverage',
            'item_nature' => 'readymade',
            'prep_time_minutes' => 0,
            'is_available' => true,
        ]);
    }
}

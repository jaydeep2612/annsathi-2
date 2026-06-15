<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Restaurant;
use App\Domains\Tax\Models\TaxGroup;
use App\Domains\Tax\Models\TaxRate;
use App\Domains\Tax\Services\TaxCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $restaurant;
    protected $taxGroup;
    protected $cgstRate;
    protected $sgstRate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::create([
            'name' => 'Pizza Palace',
            'slug' => 'pizza-palace',
            'subscription_plan' => 'pro',
            'is_active' => true,
        ]);

        // Create CGST Rate
        $this->cgstRate = TaxRate::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'CGST 2.5%',
            'rate' => 2.50,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        // Create SGST Rate
        $this->sgstRate = TaxRate::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'SGST 2.5%',
            'rate' => 2.50,
            'type' => 'percentage',
            'is_active' => true,
        ]);

        // Create Tax Group
        $this->taxGroup = TaxGroup::create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'GST 5%',
            'is_active' => true,
        ]);

        // Attach rates to group
        $this->taxGroup->rates()->attach([$this->cgstRate->id, $this->sgstRate->id]);
    }

    /**
     * Test exclusive tax calculation
     */
    public function test_exclusive_tax_calculation(): void
    {
        $service = app(TaxCalculationService::class);

        $result = $service->calculate(200.00, $this->taxGroup, false);

        $this->assertEquals(200.00, $result['subtotal']);
        $this->assertEquals(10.00, $result['tax_total']);
        $this->assertEquals(210.00, $result['grand_total']);
        $this->assertCount(2, $result['details']);

        // Check details breakdown
        $cgstDetail = collect($result['details'])->firstWhere('tax_rate_id', $this->cgstRate->id);
        $this->assertNotNull($cgstDetail);
        $this->assertEquals(5.00, $cgstDetail['amount']);

        $sgstDetail = collect($result['details'])->firstWhere('tax_rate_id', $this->sgstRate->id);
        $this->assertNotNull($sgstDetail);
        $this->assertEquals(5.00, $sgstDetail['amount']);
    }

    /**
     * Test inclusive tax calculation
     */
    public function test_inclusive_tax_calculation(): void
    {
        $service = app(TaxCalculationService::class);

        $result = $service->calculate(210.00, $this->taxGroup, true);

        $this->assertEquals(200.00, $result['subtotal']);
        $this->assertEquals(10.00, $result['tax_total']);
        $this->assertEquals(210.00, $result['grand_total']);
        $this->assertCount(2, $result['details']);

        // Check details breakdown
        $cgstDetail = collect($result['details'])->firstWhere('tax_rate_id', $this->cgstRate->id);
        $this->assertNotNull($cgstDetail);
        $this->assertEquals(5.00, $cgstDetail['amount']);

        $sgstDetail = collect($result['details'])->firstWhere('tax_rate_id', $this->sgstRate->id);
        $this->assertNotNull($sgstDetail);
        $this->assertEquals(5.00, $sgstDetail['amount']);
    }
}

<?php

namespace YourVendor\UserDiscounts\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use YourVendor\UserDiscounts\Models\Discount;
use YourVendor\UserDiscounts\Models\UserDiscount;
use YourVendor\UserDiscounts\Services\DiscountService;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    private DiscountService $discountService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discountService = app(DiscountService::class);
    }

    public function test_usage_cap_enforcement()
    {
        // Create a discount with usage cap
        $discount = Discount::create([
            'name' => 'Test Discount',
            'code' => 'TEST10',
            'type' => 'percentage',
            'value' => 10,
            'max_uses' => 2, // Only 2 uses allowed
            'is_active' => true,
        ]);

        $userDiscount = UserDiscount::create([
            'user_id' => 1,
            'discount_id' => $discount->id,
            'max_uses' => 3, // User can use it 3 times
            'assigned_at' => now(),
        ]);

        // First application should work
        $result1 = $this->discountService->apply(1, 100.00);
        $this->assertEquals(90.00, $result1['final_amount']);
        $this->assertCount(1, $result1['applied_discounts']);

        // Second application should work
        $result2 = $this->discountService->apply(1, 100.00);
        $this->assertEquals(90.00, $result2['final_amount']);

        // Third application should NOT work (global cap reached)
        $result3 = $this->discountService->apply(1, 100.00);
        $this->assertEquals(100.00, $result3['final_amount']);
        $this->assertCount(0, $result3['applied_discounts']);

        // Verify usage counts
        $discount->refresh();
        $userDiscount->refresh();
        
        $this->assertEquals(2, $discount->uses); // Hit global cap
        $this->assertEquals(2, $userDiscount->uses); // Only 2 uses recorded
    }

    public function test_deterministic_stacking()
    {
        // Create multiple discounts
        $discount1 = Discount::create([
            'name' => 'Fixed Discount',
            'code' => 'FIXED5',
            'type' => 'fixed',
            'value' => 5,
            'is_active' => true,
        ]);

        $discount2 = Discount::create([
            'name' => 'Percentage Discount',
            'code' => 'PERC10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        UserDiscount::create([
            'user_id' => 1,
            'discount_id' => $discount1->id,
            'assigned_at' => now(),
        ]);

        UserDiscount::create([
            'user_id' => 1,
            'discount_id' => $discount2->id,
            'assigned_at' => now(),
        ]);

        // Apply discounts - should be deterministic
        $result = $this->discountService->apply(1, 100.00);
        
        // Fixed discount applied first: 100 - 5 = 95
        // Then percentage: 95 - (10% of 95) = 85.5
        $this->assertEquals(85.50, $result['final_amount']);
        $this->assertCount(2, $result['applied_discounts']);
    }

    public function test_max_percentage_cap()
    {
        // Create multiple percentage discounts that would exceed cap
        $discount1 = Discount::create([
            'name' => 'Big Discount',
            'code' => 'BIG50',
            'type' => 'percentage',
            'value' => 50,
            'is_active' => true,
        ]);

        $discount2 = Discount::create([
            'name' => 'Another Discount',
            'code' => 'ANOTHER40',
            'type' => 'percentage',
            'value' => 40,
            'is_active' => true,
        ]);

        UserDiscount::create([
            'user_id' => 1,
            'discount_id' => $discount1->id,
            'assigned_at' => now(),
        ]);

        UserDiscount::create([
            'user_id' => 1,
            'discount_id' => $discount2->id,
            'assigned_at' => now(),
        ]);

        $result = $this->discountService->apply(1, 100.00);
        
        // Should cap at 80% total discount (config default)
        $this->assertEquals(20.00, $result['final_amount']);
        $this->assertCount(2, $result['applied_discounts']);
        
        // First discount: 50% of 100 = 50
        // Second discount: 30% of 50 = 15 (capped at 30% because 50+30=80%)
        $this->assertEquals(50.00, $result['applied_discounts'][0]['amount']);
        $this->assertEquals(15.00, $result['applied_discounts'][1]['amount']);
    }
}
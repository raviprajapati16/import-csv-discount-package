<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Vendor\UserDiscounts\Models\Discount;

class DiscountTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample discounts
        $discounts = [
            [
                'name' => 'Welcome Discount',
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10,
                'max_uses' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fixed Amount Discount',
                'code' => 'SAVE5',
                'type' => 'fixed',
                'value' => 5,
                'max_uses' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Big Sale',
                'code' => 'BIGSALE25',
                'type' => 'percentage',
                'value' => 25,
                'max_uses' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }

        $this->command->info('Sample discounts created successfully!');
    }
}

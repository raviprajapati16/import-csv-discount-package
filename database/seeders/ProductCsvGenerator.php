<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductCsvGenerator extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $header = "sku,name,description,price,quantity\n";
        $rows = [];
        
        for ($i = 1; $i <= 10000; $i++) {
            $sku = sprintf('SKU%08d', $i);
            $name = "Product {$i}";
            $description = "Description for product {$i}";
            $price = rand(100, 10000) / 100; // Random price between 1.00 and 100.00
            $quantity = rand(0, 1000);
            
            $rows[] = "{$sku},{$name},{$description},{$price},{$quantity}";
        }
        
        $content = $header . implode("\n", $rows);
        Storage::put('mock/products_10000.csv', $content);
    }
}

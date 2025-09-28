<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CsvImportService;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private CsvImportService $importService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = app(CsvImportService::class);
    }

    public function test_upsert_creates_new_products()
    {
        // Create test CSV
        $csvPath = storage_path('app/test_products.csv');
        $csvContent = "sku,name,description,price,quantity\nTEST001,Test Product,Test Description,99.99,100";
        file_put_contents($csvPath, $csvContent);

        $results = $this->importService->import($csvPath);

        $this->assertEquals(1, $results['imported']);
        $this->assertEquals(0, $results['updated']);
        $this->assertDatabaseHas('products', [
            'sku' => 'TEST001',
            'name' => 'Test Product',
            'price' => 99.99
        ]);

        unlink($csvPath);
    }

    public function test_upsert_updates_existing_products()
    {
        // Create existing product
        Product::create([
            'sku' => 'EXIST001',
            'name' => 'Existing Product',
            'price' => 50.00,
            'quantity' => 10
        ]);

        // Create test CSV with updated data
        $csvPath = storage_path('app/test_products.csv');
        $csvContent = "sku,name,description,price,quantity\nEXIST001,Updated Product,Updated Description,75.00,25";
        file_put_contents($csvPath, $csvContent);

        $results = $this->importService->import($csvPath);

        $this->assertEquals(0, $results['imported']);
        $this->assertEquals(1, $results['updated']);
        $this->assertDatabaseHas('products', [
            'sku' => 'EXIST001',
            'name' => 'Updated Product',
            'price' => 75.00,
            'quantity' => 25
        ]);

        unlink($csvPath);
    }

    public function test_import_handles_invalid_rows()
    {
        $csvPath = storage_path('app/test_products.csv');
        $csvContent = "sku,name,description,price,quantity\n,Invalid Product,,abc,xyz";
        file_put_contents($csvPath, $csvContent);

        $results = $this->importService->import($csvPath);

        $this->assertEquals(1, $results['invalid']);
        $this->assertCount(1, $results['errors']);

        unlink($csvPath);
    }

    public function test_import_handles_duplicate_skus()
    {
        $csvPath = storage_path('app/test_products.csv');
        $csvContent = "sku,name,description,price,quantity\nDUP001,Product 1,,10,10\nDUP001,Product 2,,20,20";
        file_put_contents($csvPath, $csvContent);

        $results = $this->importService->import($csvPath);

        $this->assertEquals(1, $results['duplicates']);
        $this->assertEquals(1, $results['imported']);

        unlink($csvPath);
    }
}
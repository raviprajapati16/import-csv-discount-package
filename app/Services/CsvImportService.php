<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CsvImportService
{
    private bool $hasMissingColumns = false;
    private array $missingColumns = [];
    private array $results = [
        'total' => 0,
        'imported' => 0,
        'updated' => 0,
        'invalid' => 0,
        'duplicates' => 0,
        'errors' => []
    ];

    private array $requiredColumns = ['sku', 'name', 'price', 'quantity'];

    public function import(string $filePath): array
    {
        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);
        
        $this->validateHeaders($headers);

        $batchSize = 1000;
        $batch = [];
        $processedSkus = [];

        while (($row = fgetcsv($file)) !== FALSE) {
            $this->results['total']++;
            
            $data = array_combine($headers, $row);
            $data = array_map('trim', $data);

            $validation = $this->validateRow($data);
            
            if ($validation->fails()) {
                $this->results['invalid']++;
                $this->results['errors'][] = "Row {$this->results['total']}: " . implode(', ', $validation->errors()->all());
                continue;
            }

            if (in_array($data['sku'], $processedSkus)) {
                $this->results['duplicates']++;
                continue;
            }

            $processedSkus[] = $data['sku'];
            $batch[] = $data;

            if (count($batch) >= $batchSize) {
                $this->processBatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->processBatch($batch);
        }

        fclose($file);
        return $this->results;
    }

    private function validateHeaders(array $headers): void
    {
        $missingColumns = array_diff($this->requiredColumns, $headers);
        
        if (!empty($missingColumns)) {
            if (!empty($missingColumns)) {
                $this->hasMissingColumns = true;
                $this->missingColumns = $missingColumns;
            }
            // throw new \Exception("Missing required columns: " . implode(', ', $missingColumns));
        }
    }

    private function validateRow(array $data): \Illuminate\Contracts\Validation\Validator
    {
        if ($this->hasMissingColumns) {
            return Validator::make([], [], [])->after(function($validator) {
                $validator->errors()->add('columns', 'Missing required columns: ' . implode(', ', $this->missingColumns));
            });
        }
        return Validator::make($data, [
            'sku' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0'
        ]);
    }

    private function processBatch(array $batch): void
    {
        DB::transaction(function () use ($batch) {
            foreach ($batch as $data) {
                $existingProduct = Product::where('sku', $data['sku'])->first();

                if ($existingProduct) {
                    $existingProduct->update($data);
                    $this->results['updated']++;
                } else {
                    Product::create($data);
                    $this->results['imported']++;
                }
            }
        });
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CsvImportController extends Controller
{
    public function import(Request $request, CsvImportService $importService): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:102400' // 100MB max
        ]);

        try {
            $filePath = $request->file('csv_file')->store('temp');
            $fullPath = Storage::path($filePath);

            $results = $importService->import($fullPath);

            unlink($fullPath);

            return response()->json([
                'success' => true,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

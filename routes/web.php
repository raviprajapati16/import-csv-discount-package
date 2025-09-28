<?php

use App\Http\Controllers\ChunkedUploadController;
use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountTestController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::prefix('api')->group(function () {
    // CSV Import
    Route::post('/import/csv', [CsvImportController::class, 'import']);
    
    // Chunked Upload
    Route::prefix('upload')->group(function () {
        Route::post('/init', [ChunkedUploadController::class, 'init']);
        Route::post('/{uploadId}/chunk', [ChunkedUploadController::class, 'uploadChunk']);
        Route::post('/{uploadId}/complete', [ChunkedUploadController::class, 'complete']);
        Route::get('/{uploadId}/progress', [ChunkedUploadController::class, 'progress']);
    });
});


Route::prefix('discount-test')->group(function () {
    Route::get('/', [DiscountTestController::class, 'index'])->name('discount-test.index');
    Route::post('/assign', [DiscountTestController::class, 'assignDiscount'])->name('discount-test.assign');
    Route::post('/apply', [DiscountTestController::class, 'applyDiscount'])->name('discount-test.apply');
    Route::post('/revoke', [DiscountTestController::class, 'revokeDiscount'])->name('discount-test.revoke');
    Route::get('/eligible', [DiscountTestController::class, 'getEligible'])->name('discount-test.eligible');
    Route::post('/create-discount', [DiscountTestController::class, 'createDiscount'])->name('discount-test.create-discount');

    // User management routes
    Route::post('/set-user', [DiscountTestController::class, 'setUser'])->name('discount-test.set-user');
    Route::get('/users', [DiscountTestController::class, 'getUsers'])->name('discount-test.users');
});
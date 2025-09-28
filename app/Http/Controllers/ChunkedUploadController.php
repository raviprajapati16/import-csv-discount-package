<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use App\Models\Upload;
use App\Services\ChunkedUploadService;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChunkedUploadController extends Controller
{
    public function init(Request $request, ChunkedUploadService $uploadService): JsonResponse
    {
        $request->validate([
            'original_name' => 'required|string',
            'file_size' => 'required|integer',
            'total_chunks' => 'required|integer|min:1',
            'chunk_size' => 'required|integer|min:1',
            'mime_type' => 'required|string',
        ]);

        $upload = $uploadService->initUpload($request->all());

        return response()->json([
            'upload_id' => $upload->id,
            'chunk_size' => $upload->chunk_size,
        ]);
    }

    public function uploadChunk(Request $request, ChunkedUploadService $uploadService, $uploadId): JsonResponse
    {
        $request->validate([
            'chunk_index' => 'required|integer|min:0',
            'chunk_file' => 'required|file',
        ]);

        $upload = Upload::findOrFail($uploadId);

        if ($upload->isCompleted()) {
            return response()->json(['message' => 'Upload already completed'], 400);
        }

        $uploadService->processChunk(
            $upload,
            $request->file('chunk_file'),
            $request->chunk_index
        );

        return response()->json([
            'message' => 'Chunk uploaded successfully',
            'progress' => $uploadService->getUploadProgress($upload)
        ]);
    }

    public function complete(Request $request, ChunkedUploadService $uploadService, ImageProcessingService $imageService, $uploadId): JsonResponse
    {
        $request->validate([
            'checksum' => 'required|string',
            'product_sku' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $upload = Upload::findOrFail($uploadId);
            $product = Product::where('sku', $request->product_sku)->firstOrFail();

            if ($upload->isCompleted()) {
                $existingImage = Image::where('upload_id', $upload->id)->first();
                if ($existingImage) {
                    return response()->json([
                        'message' => 'Upload already processed',
                        'image_id' => $existingImage->id,
                        'variants' => $existingImage->variants
                    ]);
                }
            }

            $uploadService->completeUpload($upload, $request->checksum);

            $filePath = Storage::path($upload->file_path);
            if (!file_exists($filePath)) {
                throw new \Exception('Uploaded file not found');
            }

            $variants = $imageService->generateVariants($filePath, $upload->file_name);

            $image = Image::create([
                'original_name' => $upload->original_name,
                'file_name' => $upload->file_name,
                'file_path' => $upload->file_path,
                'mime_type' => $upload->mime_type,
                'file_size' => $upload->file_size,
                'checksum' => $upload->checksum,
                'variants' => $variants,
                'upload_id' => $upload->id,
                'product_id' => $product->id,
                'is_primary' => !$product->images()->exists(),
            ]);

            if ($image->is_primary) {
                $product->update(['primary_image_id' => $image->id]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Upload completed successfully',
                'image_id' => $image->id,
                'variants' => $variants
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Upload completion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function progress($uploadId, ChunkedUploadService $uploadService): JsonResponse
    {
        $upload = Upload::findOrFail($uploadId);
        $progress = $uploadService->getUploadProgress($upload);

        return response()->json($progress);
    }
}

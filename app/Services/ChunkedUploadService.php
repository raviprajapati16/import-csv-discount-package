<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkedUploadService
{
    public function initUpload(array $data): Upload
    {
        return Upload::create([
            'original_name' => $data['original_name'],
            'file_name' => Str::random(40) . '.' . pathinfo($data['original_name'], PATHINFO_EXTENSION),
            'file_path' => 'uploads/' . Str::random(40),
            'mime_type' => $data['mime_type'],
            'file_size' => $data['file_size'],
            'total_chunks' => $data['total_chunks'],
            'chunk_size' => $data['chunk_size'],
            'status' => 'uploading',
        ]);
    }

    public function processChunk(Upload $upload, $chunkFile, int $chunkIndex): bool
    {
        $chunkPath = "chunks/{$upload->id}/{$chunkIndex}";
        
        Storage::put($chunkPath, file_get_contents($chunkFile));
        
        $upload->addUploadedChunk($chunkIndex);
        $upload->save();

        return true;
    }

    public function completeUpload(Upload $upload, string $expectedChecksum): bool
    {
        $filePath = $this->combineChunks($upload);
        
        $actualChecksum = md5_file(Storage::path($filePath));
        
        if ($actualChecksum !== $expectedChecksum) {
            Storage::delete($filePath);
            throw new \Exception('Checksum mismatch');
        }

        $upload->update([
            'checksum' => $actualChecksum,
            'status' => 'completed',
            'file_path' => $filePath,
        ]);

        $this->cleanupChunks($upload);

        return true;
    }

    private function combineChunks(Upload $upload): string
    {
        $finalPath = "uploads/{$upload->file_name}";
        $finalStoragePath = Storage::path($finalPath);

        // Ensure directory exists
        Storage::makeDirectory(dirname($finalPath));

        $finalFile = fopen($finalStoragePath, 'wb');

        for ($i = 0; $i < $upload->total_chunks; $i++) {
            $chunkPath = "chunks/{$upload->id}/{$i}";
            
            // Check if chunk exists
            if (!Storage::exists($chunkPath)) {
                throw new \Exception("Chunk {$i} not found for upload {$upload->id}");
            }
            
            $chunkContent = Storage::get($chunkPath);
            fwrite($finalFile, $chunkContent);
        }

        fclose($finalFile);
        return $finalPath;
    }

    private function cleanupChunks(Upload $upload): void
    {
        Storage::deleteDirectory("chunks/{$upload->id}");
    }

    public function getUploadProgress(Upload $upload): array
    {
        return [
            'uploaded_chunks' => $upload->uploaded_chunks,
            'total_chunks' => $upload->total_chunks,
            'progress' => ($upload->uploaded_chunks / $upload->total_chunks) * 100,
            'chunks_uploaded' => $upload->getUploadedChunksArray(),
        ];
    }
}
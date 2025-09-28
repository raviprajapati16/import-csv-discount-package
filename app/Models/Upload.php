<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Upload extends Model
{
     use HasFactory;

    protected $fillable = [
        'original_name', 'file_name', 'file_path', 'mime_type', 'file_size',
        'checksum', 'total_chunks', 'chunk_size', 'uploaded_chunks',
        'status', 'chunks_uploaded', 'product_id'
    ];

    protected $casts = [
        'chunks_uploaded' => 'array',
        'file_size' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): HasOne
    {
        return $this->hasOne(Image::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getUploadedChunksArray(): array
    {
        return $this->chunks_uploaded ?? [];
    }

    public function addUploadedChunk(int $chunkIndex): void
    {
        $chunks = $this->getUploadedChunksArray();
        if (!in_array($chunkIndex, $chunks)) {
            $chunks[] = $chunkIndex;
            $this->chunks_uploaded = $chunks;
            $this->uploaded_chunks = count($chunks);
            $this->save();
        }
    }
}

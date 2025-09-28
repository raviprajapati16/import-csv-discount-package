<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
     use HasFactory;

    protected $fillable = [
        'original_name', 'file_name', 'file_path', 'mime_type', 'file_size',
        'checksum', 'variants', 'upload_id', 'product_id', 'is_primary'
    ];

    protected $casts = [
        'variants' => 'array',
        'is_primary' => 'boolean',
        'file_size' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function getVariantPath(string $size): ?string
    {
        return $this->variants[$size] ?? null;
    }

    // Accessor for easy variant URLs
    public function getVariantUrlAttribute(): array
    {
        $urls = [];
        if ($this->variants) {
            foreach ($this->variants as $size => $path) {
                $urls[$size] = asset('storage/' . $path);
            }
        }
        return $urls;
    }

    // Accessor for original image URL
    public function getOriginalUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}

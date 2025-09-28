<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'quantity',
        'primary_image_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Use belongsTo for primary_image_id since it references the images table
    public function primaryImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'primary_image_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    // Helper method to set primary image safely
    public function setPrimaryImage(Image $image): void
    {
        // Ensure the image belongs to this product
        if ($image->product_id !== $this->id) {
            throw new \Exception('Image does not belong to this product');
        }

        // Update all images to not primary
        $this->images()->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);
        $this->update(['primary_image_id' => $image->id]);
    }
}

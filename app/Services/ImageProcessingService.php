<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class ImageProcessingService
{
    private array $sizes = [
        'thumbnail' => 256,
        'medium' => 512,
        'large' => 1024
    ];

     private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function generateVariants(string $originalPath, string $fileName): array
    {
        $variants = [];
        
        if (!file_exists($originalPath)) {
            throw new \Exception("Original image file not found: {$originalPath}");
        }

        $originalImage = $this->imageManager->read($originalPath);

        foreach ($this->sizes as $name => $size) {
            $variant = $this->resizeImage($originalImage, $size);
            $variantPath = $this->saveVariant($variant, $fileName, $name);
            $variants[$name] = $variantPath;
        }

        return $variants;
    }

    private function resizeImage($image, int $size)
    {
        return $image->scaleDown($size, $size);
    }

    private function saveVariant($image, string $fileName, string $size): string
    {
        $variantFileName = pathinfo($fileName, PATHINFO_FILENAME) . "_{$size}." . pathinfo($fileName, PATHINFO_EXTENSION);
        $path = "images/variants/{$variantFileName}";
        
        Storage::disk('public')->makeDirectory('images/variants');
        
        $encodedImage = $image->encodeByExtension(pathinfo($fileName, PATHINFO_EXTENSION));
        Storage::disk('public')->put($path, $encodedImage);
        
        return $path;
    }
}
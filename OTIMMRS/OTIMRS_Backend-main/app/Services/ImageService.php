<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService
{
    public function uploadImage($image, $path = 'attractions')
    {
        if (!$image) {
            return null;
        }

        // Generate unique filename
        $filename = Str::random(40) . '.' . $image->getClientOriginalExtension();
        
        // Create image instance and resize
        $img = Image::make($image);
        
        // Resize image maintaining aspect ratio
        $img->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Create thumbnail
        $thumbnail = Image::make($image)->fit(200, 200);

        // Store images
        $mainPath = "{$path}/{$filename}";
        $thumbnailPath = "{$path}/thumbnails/{$filename}";

        Storage::put("public/{$mainPath}", (string) $img->encode());
        Storage::put("public/{$thumbnailPath}", (string) $thumbnail->encode());

        return [
            'main_image' => Storage::url($mainPath),
            'thumbnail' => Storage::url($thumbnailPath)
        ];
    }

    public function deleteImage($path)
    {
        if (!$path) {
            return;
        }

        $mainPath = str_replace('/storage/', 'public/', $path);
        $thumbnailPath = str_replace('/storage/', 'public/thumbnails/', $path);

        Storage::delete([$mainPath, $thumbnailPath]);
    }
}

<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\ImageTransformation;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

    public function uploadImage($path, $fileContent)
    {
        if (strpos($fileContent, "\0") !== false) {
            return response()->json(['error' => 'El contenido del archivo contiene bytes nulos'], 500);
        }
        $upload = $this->cloudinary->uploadApi()->upload($fileContent, [
            'public_id' => $path,
            'overwrite' => true,
        ]);
        
        return $upload['secure_url'];
    }
}

<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key'    => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    /**
     * Sube una imagen a Cloudinary y devuelve la secure_url
     *
     * @param UploadedFile $file
     * @param string $folder
     * @return string secure_url
     */
    public function uploadImage(UploadedFile $file, string $folder = 'dejandohuella') : string
    {
        $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'image',
            'public_id' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '-' . uniqid(),
            'overwrite' => true,
        ]);
        return $result['secure_url'] ?? '';
    }
}

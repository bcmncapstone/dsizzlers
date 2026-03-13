<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private function client(): Cloudinary
    {
        return new Cloudinary([
            'cloud' => [
                'cloud_name' => (string) config('services.cloudinary.cloud_name'),
                'api_key' => (string) config('services.cloudinary.api_key'),
                'api_secret' => (string) config('services.cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    public function isConfigured(): bool
    {
        return (bool) config('services.cloudinary.cloud_name')
            && (bool) config('services.cloudinary.api_key')
            && (bool) config('services.cloudinary.api_secret');
    }

    /**
     * Uploads a file to Cloudinary and returns metadata.
     *
     * @return array{secure_url:string, public_id:string}
     */
    public function upload(UploadedFile $file, string $folder = 'uploads', string $resourceType = 'auto'): array
    {
        $result = $this->client()->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => $resourceType,
            'use_filename' => true,
            'unique_filename' => true,
        ]);

        return [
            'secure_url' => (string) ($result['secure_url'] ?? ''),
            'public_id' => (string) ($result['public_id'] ?? ''),
        ];
    }

    public function deleteByUrl(string $url, string $resourceType = 'image'): void
    {
        $publicId = $this->extractPublicIdFromUrl($url);
        if (!$publicId) {
            return;
        }

        $this->client()->uploadApi()->destroy($publicId, [
            'resource_type' => $resourceType,
        ]);
    }

    private function extractPublicIdFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        if (!isset($parsed['path'])) {
            return null;
        }

        $path = $parsed['path'];
        $marker = '/upload/';
        $pos = strpos($path, $marker);

        if ($pos === false) {
            return null;
        }

        $assetPath = substr($path, $pos + strlen($marker));

        // Remove optional version prefix like v1700000000/
        $assetPath = preg_replace('#^v\d+/#', '', $assetPath) ?? $assetPath;

        // Remove extension, preserve nested folder structure
        $dotPos = strrpos($assetPath, '.');
        if ($dotPos !== false) {
            $assetPath = substr($assetPath, 0, $dotPos);
        }

        return trim($assetPath) !== '' ? $assetPath : null;
    }
}

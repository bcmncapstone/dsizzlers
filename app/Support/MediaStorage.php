<?php

namespace App\Support;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MediaStorage
{
    public function __construct(private readonly Cloudinary $cloudinary)
    {
    }

    public static function upload(UploadedFile $file, string $folder): string
    {
        return app(self::class)->uploadFile($file, $folder);
    }

    public static function url(?string $path): ?string
    {
        return app(self::class)->resolveUrl($path);
    }

    public static function delete(?string $path): void
    {
        app(self::class)->deleteFile($path);
    }

    public static function isRemote(?string $path): bool
    {
        return app(self::class)->isRemoteUrl($path);
    }

    public static function previewResponse(string $path, ?string $absoluteLocalPath = null)
    {
        return app(self::class)->createPreviewResponse($path, $absoluteLocalPath);
    }

    public static function downloadResponse(string $path, ?string $absoluteLocalPath = null, ?string $downloadName = null)
    {
        return app(self::class)->createDownloadResponse($path, $absoluteLocalPath, $downloadName);
    }

    public function uploadFile(UploadedFile $file, string $folder): string
    {
        $mimeType = strtolower((string) $file->getMimeType());
        $resourceType = str_starts_with($mimeType, 'image/') ? 'image' : 'raw';

        $upload = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder' => $this->buildFolder($folder),
            'resource_type' => $resourceType,
            'use_filename' => true,
            'unique_filename' => true,
            'filename_override' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
        ]);

        $secureUrl = $upload['secure_url'] ?? null;

        if (! is_string($secureUrl) || $secureUrl === '') {
            throw new RuntimeException('Cloudinary did not return a secure URL for the uploaded file.');
        }

        return $secureUrl;
    }

    public function resolveUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = trim($path);

        if ($this->isRemoteUrl($path)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function deleteFile(?string $path): void
    {
        if (! is_string($path) || trim($path) === '') {
            return;
        }

        $path = trim($path);

        if ($this->isRemoteUrl($path)) {
            $asset = $this->parseCloudinaryUrl($path);

            if ($asset !== null) {
                $this->cloudinary->uploadApi()->destroy($asset['public_id'], [
                    'resource_type' => $asset['resource_type'],
                    'invalidate' => true,
                ]);
            }

            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function isRemoteUrl(?string $path): bool
    {
        return is_string($path) && filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    public function createPreviewResponse(string $path, ?string $absoluteLocalPath = null)
    {
        if ($this->isRemoteUrl($path)) {
            // Redirect directly to the Cloudinary URL for inline viewing.
            // Do NOT use privateDownloadUrl here — it forces a download.
            return redirect()->away($path);
        }

        if ($absoluteLocalPath === null || ! file_exists($absoluteLocalPath)) {
            abort(404, 'File not found.');
        }

        return response()->file($absoluteLocalPath);
    }

    public function createDownloadResponse(string $path, ?string $absoluteLocalPath = null, ?string $downloadName = null)
    {
        if ($this->isRemoteUrl($path)) {
            // Get a signed URL to bypass Cloudinary's 401 on direct access,
            // then proxy the file through PHP so we control Content-Disposition.
            $signedUrl = $this->cloudinarySignedDownloadUrl($path);
            return $this->downloadRemoteFile($signedUrl ?? $path, $downloadName);
        }

        if ($absoluteLocalPath === null || ! file_exists($absoluteLocalPath)) {
            abort(404, 'File not found.');
        }

        return response()->download($absoluteLocalPath, $downloadName);
    }

    private function downloadRemoteFile(string $url, ?string $downloadName = null)
    {
        $remoteResponse = Http::timeout(60)->get($url);

        if (! $remoteResponse->successful()) {
            abort(404, 'File not found.');
        }

        $filename = $downloadName ?: basename(parse_url($url, PHP_URL_PATH) ?: 'download');
        $filename = str_replace('"', '', $filename);

        return response($remoteResponse->body(), 200, [
            'Content-Type' => $remoteResponse->header('Content-Type') ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildFolder(string $folder): string
    {
        $baseFolder = trim((string) config('services.cloudinary.folder'), '/');
        $folder = trim($folder, '/');

        return trim(($baseFolder !== '' ? $baseFolder . '/' : '') . $folder, '/');
    }

    private function parseCloudinaryUrl(string $url): ?array
    {
        $pattern = '#^https?://res\.cloudinary\.com/[^/]+/(?P<resource_type>image|video|raw)/upload/(?:v\d+/)?(?P<public_id>.+)$#';

        if (! preg_match($pattern, $url, $matches)) {
            return null;
        }

        $publicId = $matches['public_id'];
        $resourceType = $matches['resource_type'];

        if ($resourceType !== 'raw') {
            $extension = pathinfo($publicId, PATHINFO_EXTENSION);

            if ($extension !== '') {
                $publicId = substr($publicId, 0, -1 * (strlen($extension) + 1));
            }
        }

        return [
            'public_id' => $publicId,
            'resource_type' => $resourceType,
        ];
    }

    private function cloudinarySignedDownloadUrl(string $url): ?string
    {
        if (! str_contains($url, 'res.cloudinary.com')) {
            return null;
        }

        $asset = $this->parseCloudinaryUrl($url);

        if ($asset === null) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $format = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if ($format === '') {
            return null;
        }

        $options = [
            'resource_type' => $asset['resource_type'],
            'type' => 'upload',
        ];

        try {
            return $this->cloudinary->uploadApi()->privateDownloadUrl($asset['public_id'], $format, $options);
        } catch (Throwable) {
            return null;
        }
    }
}
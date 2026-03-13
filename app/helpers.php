<?php

if (!function_exists('media_url')) {
    function media_url(?string $path): string
    {
        $path = (string) ($path ?? '');

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Normalize legacy/local formats so we do not generate duplicate segments.
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        // Use relative URL to avoid APP_URL host/port mismatches.
        return '/storage/' . $path;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketingDownloadController extends Controller
{
    /**
     * Proxy download for marketing images (Cloudinary or local).
     *
     * @param  Request  $request
     * @return StreamedResponse
     */
    public function download(Request $request)
    {
        $url = $request->query('url');
        $filename = $request->query('filename', 'marketing-image.jpg');
        if (!$url) {
            \Log::error('Marketing download failed: missing URL');
            abort(404);
            throw new \RuntimeException('Unreachable');
        }
        $decodedUrl = urldecode($url);
        try {
            $response = Http::timeout(60)->withOptions(['stream' => true])->get($decodedUrl);
            if (!$response->successful()) {
                \Log::error('Marketing download failed: HTTP error', ['url' => $decodedUrl, 'status' => $response->status()]);
                abort(404);
            }
            $contentType = $response->header('Content-Type') ?: 'application/octet-stream';
            return response()->stream(function () use ($response) {
                $body = $response->toPsrResponse()->getBody();
                while (!$body->eof()) {
                    echo $body->read(4096);
                }
            }, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            \Log::error('Marketing download exception', ['error' => $e->getMessage(), 'url' => $decodedUrl]);
            abort(404);
            throw new \RuntimeException('Unreachable');
        }
        abort(404);
        throw new \RuntimeException('Unreachable');
    }
}

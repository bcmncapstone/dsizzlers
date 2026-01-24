<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Message;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

class ProcessChatImage implements ShouldQueue
{
    use Queueable;

    protected $messageId;
    protected $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct($messageId, $filePath)
    {
        $this->messageId = $messageId;
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = Message::find($this->messageId);

        if (!$message || !$this->filePath) {
            return;
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($this->filePath)) {
            return;
        }

        try {
            // Get the file from storage
            $fileContent = Storage::disk('public')->get($this->filePath);
            $mimeType = $message->file_type;

            // Process image
            $manager = ImageManager::gd();
            $image = $manager->read($fileContent);

            // Resize image if it's too large (max width/height 1200px, maintain aspect ratio)
            $image->scale(width: 1200);

            // Compress based on format
            $fileName = $message->file_name;
            if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
                $encoded = $image->toJpeg(80); // 80% quality
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
            } elseif ($mimeType === 'image/png') {
                $encoded = $image->toPng(); // PNG compression
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.png';
            } elseif ($mimeType === 'image/webp') {
                $encoded = $image->toWebp(80); // 80% quality
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
            } else {
                // For other image formats, convert to JPEG
                $encoded = $image->toJpeg(80);
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
                $mimeType = 'image/jpeg';
            }

            // Save processed image back to the SAME PATH so the URL never changes
            Storage::disk('public')->put($this->filePath, (string) $encoded);

            // Keep original metadata; only update mime if format changed
            $message->update([
                'file_type' => $mimeType
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail - keep original file if processing fails
            \Log::error('Image processing failed for message ' . $this->messageId . ': ' . $e->getMessage());
        }
    }
}

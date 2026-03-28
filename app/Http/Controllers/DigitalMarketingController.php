<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DigitalMarketingUpload;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DigitalMarketingController extends Controller
{
    public function __construct(private CloudinaryService $cloudinary)
    {
    }

    public function index()
    {
        return view('communication.digital', [
            'uploads' => DigitalMarketingUpload::query()->notArchived()->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin) {
                return back()
                    ->with('error', 'Only administrators can upload digital marketing posts.')
                    ->with('flash_timeout', 3000);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'description' => 'nullable|string|max:500'
            ]);

            if ($this->cloudinary->isConfigured()) {
                $upload = $this->cloudinary->upload($request->file('image'), 'digital_marketing', 'image');
                $path = $upload['secure_url'];
            } else {
                $path = $request->file('image')->store('digital_marketing', 'public');
            }

            DigitalMarketingUpload::create([
                'uploaded_by' => $admin->admin_id,
                'image_path' => $path,
                'description' => $request->description
            ]);

            return back()
                ->with('success', 'Digital marketing post uploaded successfully!')
                ->with('flash_timeout', 3000);
            
        } catch (\Exception $e) {
            Log::error('Digital marketing upload failed', ['error' => $e->getMessage()]);
            return back()
                ->with('error', 'Failed to upload post: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin) {
                return back()
                    ->with('error', 'Unauthorized action.')
                    ->with('flash_timeout', 3000);
            }

            $request->validate([
                'description' => 'nullable|string|max:500'
            ]);

            $post = DigitalMarketingUpload::findOrFail($id);
            $post->update([
                'description' => $request->description
            ]);

            return back()
                ->with('success', 'Post updated successfully!')
                ->with('flash_timeout', 3000);
            
        } catch (\Exception $e) {
            Log::error('Digital marketing update failed', ['error' => $e->getMessage()]);
            return back()
                ->with('error', 'Failed to update post: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }

    public function destroy($id)
    {
        try {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin) {
                return back()
                    ->with('error', 'Unauthorized action.')
                    ->with('flash_timeout', 3000);
            }

            $post = DigitalMarketingUpload::findOrFail($id);

            // Archive only (do not permanently delete), following existing JSON archive process.
            DigitalMarketingUpload::archiveId((int) $post->id);

            return back()
                ->with('success', 'Post archived successfully!')
                ->with('flash_timeout', 3000);
            
        } catch (\Exception $e) {
            Log::error('Digital marketing archive failed', ['error' => $e->getMessage()]);
            return back()
                ->with('error', 'Failed to archive post: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }

    public function restore($id)
    {
        try {
            $admin = auth()->guard('admin')->user();

            if (! $admin) {
                return back()
                    ->with('error', 'Unauthorized action.')
                    ->with('flash_timeout', 3000);
            }

            $post = DigitalMarketingUpload::findOrFail($id);
            DigitalMarketingUpload::unarchiveId((int) $post->id);

            return back()
                ->with('success', 'Post restored successfully!')
                ->with('flash_timeout', 3000);
        } catch (\Exception $e) {
            Log::error('Digital marketing restore failed', ['error' => $e->getMessage()]);
            return back()
                ->with('error', 'Failed to restore post: ' . $e->getMessage())
                ->with('flash_timeout', 3000);
        }
    }
}
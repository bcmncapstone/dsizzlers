<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DigitalMarketingUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DigitalMarketingController extends Controller
{
    public function index()
    {
        return view('communication.digital', [
            'uploads' => DigitalMarketingUpload::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin) {
                return back()->with('error', 'Only administrators can upload digital marketing posts.');
            }

            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'description' => 'nullable|string|max:500'
            ]);

            $path = $request->file('image')->store('digital_marketing', 'public');

            DigitalMarketingUpload::create([
                'uploaded_by' => $admin->admin_id,
                'image_path' => $path,
                'description' => $request->description
            ]);

            return back()->with('success', 'Digital marketing post uploaded successfully!');
            
        } catch (\Exception $e) {
            Log::error('Digital marketing upload failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to upload post: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin) {
                return back()->with('error', 'Unauthorized action.');
            }

            $request->validate([
                'description' => 'nullable|string|max:500'
            ]);

            $post = DigitalMarketingUpload::findOrFail($id);
            $post->update([
                'description' => $request->description
            ]);

            return back()->with('success', 'Post updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Digital marketing update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update post: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $admin = auth()->guard('admin')->user();
            
            if (!$admin) {
                return back()->with('error', 'Unauthorized action.');
            }

            $post = DigitalMarketingUpload::findOrFail($id);
            
            // Delete the file from storage
            if (Storage::disk('public')->exists($post->image_path)) {
                Storage::disk('public')->delete($post->image_path);
            }
            
            $post->delete();

            return back()->with('success', 'Post deleted successfully!');
            
        } catch (\Exception $e) {
            Log::error('Digital marketing delete failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete post: ' . $e->getMessage());
        }
    }
}
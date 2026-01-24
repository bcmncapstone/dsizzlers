<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\DigitalMarketingUpload;

class CommunicationController extends Controller
{
    public function index()
    {
        $userId = null;
        
        if (auth()->guard('admin')->check()) {
            $userId = auth()->guard('admin')->id();
        } elseif (auth()->guard('franchisee')->check()) {
            $userId = auth()->guard('franchisee')->id();
        }

        $conversations = Conversation::where('admin_id', $userId)
            ->orWhere('franchisee_id', $userId)
            ->get();

        $digitalMarketing = DigitalMarketingUpload::latest()->get();

        return view('communication.index', compact(
            'conversations',
            'digitalMarketing'
        ));
    }

    public function start(Request $request)
    {
        // Only admin or franchisee can start
        if (auth()->guard('admin')->check()) {
            $userId = auth()->guard('admin')->id();
            $guard = 'admin';
        } elseif (auth()->guard('franchisee')->check()) {
            $userId = auth()->guard('franchisee')->id();
            $guard = 'franchisee';
        } else {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $partnerId = $request->input('partner_id');
        if (!$partnerId) {
            return redirect()->back()->with('error', 'Partner is required');
        }

        if ($guard === 'admin') {
            // partner must be a franchisee
            $partner = \App\Models\Franchisee::find($partnerId);
            if (!$partner) return redirect()->back()->with('error', 'Franchisee not found');

            $conversation = Conversation::where('admin_id', $userId)
                ->where('franchisee_id', $partnerId)
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'admin_id' => $userId,
                    'franchisee_id' => $partnerId
                ]);
            }
        } else {
            // guard is franchisee; partner must be an admin (franchisor)
            $partner = \App\Models\Admin::find($partnerId);
            if (!$partner) return redirect()->back()->with('error', 'Franchisor not found');

            $conversation = Conversation::where('franchisee_id', $userId)
                ->where('admin_id', $partnerId)
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'admin_id' => $partnerId,
                    'franchisee_id' => $userId
                ]);
            }
        }

        return redirect('/communication/' . $conversation->id);
    }
}

<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Since broadcasting uses the default guard, we need to find the actual authenticated user
    $authenticatedUser = null;
    $guards = ['admin', 'franchisor_staff', 'franchisee', 'franchisee_staff'];

    foreach ($guards as $guard) {
        if (auth($guard)->check()) {
            $authenticatedUser = auth($guard)->user();
            break;
        }
    }

    if (!$authenticatedUser) {
        return false;
    }

    return Conversation::where('id', $conversationId)
        ->where(function ($q) use ($authenticatedUser) {
            $q->where('admin_id', $authenticatedUser->id)
                ->orWhere('franchisee_id', $authenticatedUser->id);
        })
        ->exists();
});

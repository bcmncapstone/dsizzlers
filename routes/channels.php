<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Since broadcasting uses the default guard, we need to find the actual authenticated user
    $authenticatedUser = null;
    $actorType = null;
    $guards = ['admin', 'franchisor_staff', 'franchisee', 'franchisee_staff'];

    foreach ($guards as $guard) {
        if (auth($guard)->check()) {
            $authenticatedUser = auth($guard)->user();
            $actorType = in_array($guard, ['admin', 'franchisor_staff'], true) ? 'admin' : 'franchisee';
            break;
        }
    }

    if (!$authenticatedUser) {
        return false;
    }

    $actorId = $actorType === 'admin'
        ? (int) ($authenticatedUser->admin_id ?? $authenticatedUser->getKey())
        : (int) ($authenticatedUser->franchisee_id ?? $authenticatedUser->getKey());

    return Conversation::where('id', $conversationId)
        ->where(function ($q) use ($actorType, $actorId) {
            if ($actorType === 'admin') {
                $q->where('admin_id', $actorId);
                return;
            }

            $q->where('franchisee_id', $actorId);
        })
        ->exists();
});

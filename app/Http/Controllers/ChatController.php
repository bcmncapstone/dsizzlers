<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{

    protected function ensureAdminOrFranchisee()
    {
        if (auth()->guard('admin')->check()) {
            return ['guard' => 'admin', 'user' => auth()->guard('admin')->user()];
        }

        if (auth()->guard('franchisee')->check()) {
            return ['guard' => 'franchisee', 'user' => auth()->guard('franchisee')->user()];
        }

        abort(403, 'Unauthorized');
    }

    public function show(Conversation $conversation)
    {
        $actor = $this->ensureAdminOrFranchisee();
        $guard = $actor['guard'];
        $currentUser = $actor['user'];
        $currentUserType = $guard;

        if ($guard === 'admin' && $conversation->admin_id != $currentUser->getKey()) {
            abort(403, 'Forbidden');
        }

        if ($guard === 'franchisee' && $conversation->franchisee_id != $currentUser->getKey()) {
            abort(403, 'Forbidden');
        }

        $messages = $conversation->messages()->get();

        $messages->transform(function ($message) {
            $senderName = ucfirst(str_replace('_', ' ', $message->sender_type));

            if ($message->sender_type === 'admin') {
                $sender = \App\Models\Admin::find($message->sender_id);
                $senderName = $sender
                    ? trim(($sender->admin_fname ?? '') . ' ' . ($sender->admin_lname ?? '')) ?: 'System Administrator'
                    : 'System Administrator';
            } elseif ($message->sender_type === 'franchisee') {
                $sender = \App\Models\Franchisee::find($message->sender_id);
                $senderName = $sender ? ($sender->franchisee_name ?: 'Franchisee') : 'Franchisee';
            }

            $message->sender_name = $senderName;
            return $message;
        });

        $currentUserId = $currentUser ? $currentUser->getKey() : null;

        return view('communication.chat', [
            'conversation' => $conversation,
            'messages' => $messages,
            'currentUser' => $currentUser,
            'currentUserId' => $currentUserId,
            'currentUserType' => $currentUserType,
        ]);
    }

    public function fetchMessages(Request $request, Conversation $conversation)
    {
        $actor = $this->ensureAdminOrFranchisee();
        $guard = $actor['guard'];
        $currentUser = $actor['user'];

        if ($guard === 'admin' && $conversation->admin_id != $currentUser->getKey()) {
            return response()->json([], 403);
        }

        if ($guard === 'franchisee' && $conversation->franchisee_id != $currentUser->getKey()) {
            return response()->json([], 403);
        }

        $lastMessageId = $request->query('after', 0);
        $messages = $conversation->messages()->where('id', '>', $lastMessageId)->get();

        $messages->transform(function ($message) {
            $senderName = ucfirst(str_replace('_', ' ', $message->sender_type));
            if ($message->sender_type === 'admin') {
                $sender = \App\Models\Admin::find($message->sender_id);
                $senderName = $sender
                    ? trim(($sender->admin_fname ?? '') . ' ' . ($sender->admin_lname ?? '')) ?: 'System Administrator'
                    : 'System Administrator';
            } elseif ($message->sender_type === 'franchisee') {
                $sender = \App\Models\Franchisee::find($message->sender_id);
                $senderName = $sender ? ($sender->franchisee_name ?: 'Franchisee') : 'Franchisee';
            }
            $message->sender_name = $senderName;
            $message->formatted_time = $message->created_at->format('h:i A');
            return $message;
        });

        return response()->json($messages);
    }

    public function send(Request $request, Conversation $conversation)
    {
        try {
            // AUTH
            $actor = $this->ensureAdminOrFranchisee();
            $guard = $actor['guard'];
            $currentUser = $actor['user'];

            if ($guard === 'admin' && $conversation->admin_id != $currentUser->getKey()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            if ($guard === 'franchisee' && $conversation->franchisee_id != $currentUser->getKey()) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // VALIDATION - FIXED TO MATCH ACCEPTED FILE TYPES
            $validated = $request->validate([
                'message_text' => 'nullable|string|max:1000',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt|max:5120', // 5MB max
            ]);

            $senderType = $guard === 'admin' ? 'admin' : 'franchisee';
            $senderId   = $currentUser->getKey();

            // HANDLE FILE
            $filePath = null;
            $fileName = null;
            $fileType = null;

            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $fileType = $file->getMimeType();

                // Store file
                $filePath = $file->store('chat_files', 'public');
                
                Log::info('File uploaded', [
                    'path' => $filePath,
                    'name' => $fileName,
                    'type' => $fileType
                ]);
            }

            // SAVE MESSAGE
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id'       => $senderId,
                'sender_type'     => $senderType,
                'message_text'    => $request->message_text,
                'file_path'       => $filePath,
                'file_name'       => $fileName,
                'file_type'       => $fileType,
            ]);

            // BROADCAST
            try {
                broadcast(new MessageSent($message))->toOthers();
            } catch (\Exception $e) {
                Log::error('Broadcast failed', ['error' => $e->getMessage()]);
            }

            // RESPOND WITH COMPLETE DATA
            return response()->json([
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_type' => $message->sender_type,
                'sender_name' => $this->getSenderName($senderType, $senderId),
                'message_text' => $message->message_text,
                'file_path' => $message->file_path,
                'file_name' => $message->file_name,
                'file_type' => $message->file_type,
                'created_at' => $message->created_at->toIso8601String(),
                'formatted_time' => $message->created_at->format('h:i A'),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'error' => 'Validation failed',
                'message' => 'Invalid input',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Message send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getSenderName($senderType, $senderId)
    {
        if ($senderType === 'admin') {
            $sender = \App\Models\Admin::find($senderId);
            return $sender
                ? trim(($sender->admin_fname ?? '') . ' ' . ($sender->admin_lname ?? '')) ?: 'System Administrator'
                : 'System Administrator';
        } else {
            $sender = \App\Models\Franchisee::find($senderId);
            return $sender ? ($sender->franchisee_name ?: 'Franchisee') : 'Franchisee';
        }
    }
}
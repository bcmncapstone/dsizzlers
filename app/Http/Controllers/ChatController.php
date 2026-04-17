<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChatController extends Controller
{
    public function __construct(private CloudinaryService $cloudinary)
    {
    }

    protected function resolveChatActor(): array
    {
        if (auth()->guard('admin')->check()) {
            return ['guard' => 'admin', 'user' => auth()->guard('admin')->user(), 'type' => 'admin'];
        }

        if (auth()->guard('franchisor_staff')->check()) {
            $user = auth()->guard('franchisor_staff')->user();
            return ['guard' => 'franchisor_staff', 'user' => $user, 'type' => 'admin'];
        }

        if (auth()->guard('franchisee')->check()) {
            return ['guard' => 'franchisee', 'user' => auth()->guard('franchisee')->user(), 'type' => 'franchisee'];
        }

        if (auth()->guard('franchisee_staff')->check()) {
            $user = auth()->guard('franchisee_staff')->user();
            return ['guard' => 'franchisee_staff', 'user' => $user, 'type' => 'franchisee'];
        }

        abort(403, 'Unauthorized');
    }

    protected function getActorKey(array $actor): int
    {
        $user = $actor['user'];

        if ($actor['type'] === 'admin') {
            return (int) ($user->admin_id ?? $user->getKey());
        }

        return (int) ($user->franchisee_id ?? $user->getKey());
    }

    protected function getActorDisplayName(array $actor): string
    {
        $user = $actor['user'];

        if ($actor['type'] === 'admin') {
            return trim(($user->admin_fname ?? $user->astaff_fname ?? '') . ' ' . ($user->admin_lname ?? $user->astaff_lname ?? ''))
                ?: 'System Administrator';
        }

        return $user->franchisee_name
            ?? trim(($user->fstaff_fname ?? '') . ' ' . ($user->fstaff_lname ?? ''))
            ?: 'Franchisee';
    }

    protected function authorizeConversationAccess(Conversation $conversation): array
    {
        $actor = $this->resolveChatActor();
        $actorId = $this->getActorKey($actor);

        if ($actor['type'] === 'admin' && (int) $conversation->admin_id !== $actorId) {
            abort(403, 'Forbidden');
        }

        if ($actor['type'] === 'franchisee' && (int) $conversation->franchisee_id !== $actorId) {
            abort(403, 'Forbidden');
        }

        $actor['actor_id'] = $actorId;

        return $actor;
    }

    protected function buildMessagesPayload(Conversation $conversation, int $afterId = 0)
    {
        $query = $conversation->messages()
            ->orderBy('id');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        return $query->get()->map(function ($message) {
            $message->sender_name = $this->getSenderName($message->sender_type, $message->sender_id);
            $message->formatted_time = $message->created_at->format('h:i A');
            return $message;
        });
    }

    protected function resolveConversationRecord(int|string $conversationId): Conversation
    {
        return Conversation::query()->findOrFail((int) $conversationId);
    }

    public function show($conversation)
    {
        $conversation = $this->resolveConversationRecord($conversation);
        $actor = $this->authorizeConversationAccess($conversation);
        $messages = $this->buildMessagesPayload($conversation);

        return response()->view('communication.chat', [
            'conversation' => $conversation,
            'messages' => $messages,
            'currentUser' => $actor['user'],
            'currentUserId' => $actor['actor_id'],
            'currentUserType' => $actor['type'],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function fetchMessages(Request $request, $conversation)
    {
        try {
            $conversation = $this->resolveConversationRecord($conversation);
            $this->authorizeConversationAccess($conversation);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Conversation not found.'], 404);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json([], $e->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Fetch messages failed', [
                'conversation_id' => $conversation,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to load messages right now.',
                'message' => 'Please try again in a moment.'
            ], 500);
        }

        $lastMessageId = (int) $request->query('after', 0);
        $messages = $this->buildMessagesPayload($conversation, $lastMessageId);

        return response()
            ->json($messages)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function send(Request $request, $conversation)
    {
        try {
            $conversation = $this->resolveConversationRecord($conversation);

            // AUTH
            $actor = $this->authorizeConversationAccess($conversation);

            // VALIDATION - FIXED TO MATCH ACCEPTED FILE TYPES
            $validated = $request->validate([
                'message_text' => 'nullable|string|max:1000',
                'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt|max:5120', // 5MB max
            ]);

            $senderType = $actor['type'];
            $senderId   = $actor['actor_id'];
            $senderName = $this->getActorDisplayName($actor);

            // HANDLE FILE
            $filePath = null;
            $fileName = null;
            $fileType = null;

            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $fileType = $file->getMimeType();

                // Store file in Cloudinary when configured.
                if ($this->cloudinary->isConfigured()) {
                    $upload = $this->cloudinary->upload($file, 'chat_files', 'auto');
                    $filePath = $upload['secure_url'];
                } else {
                    $filePath = $file->store('chat_files', 'public');
                }
                
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
                broadcast(new MessageSent($message, $senderName))->toOthers();
            } catch (\Exception $e) {
                Log::error('Broadcast failed', ['error' => $e->getMessage()]);
            }

            // RESPOND WITH COMPLETE DATA
            return response()->json([
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'sender_type' => $message->sender_type,
                'sender_name' => $senderName,
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

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Conversation not found',
                'message' => 'This conversation could not be found.'
            ], 404);
            
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

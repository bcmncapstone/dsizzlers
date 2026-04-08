<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\DigitalMarketingUpload;
use Illuminate\Support\Facades\Storage;

class CommunicationController extends Controller
{
    public function index()
{
    $actor = $this->resolveActor();
    $conversationView = request()->query('conversation_view', 'active') === 'archived' ? 'archived' : 'active';
    $announcementView = request()->query('announcement_view', 'active') === 'archived' ? 'archived' : 'active';

    if ($actor['type'] === 'admin') {
        $conversationsQuery = Conversation::with(['admin', 'franchisee'])
            ->where('admin_id', $actor['id']);
    } else {
        $conversationsQuery = Conversation::with(['admin', 'franchisee'])
            ->where('franchisee_id', $actor['id']);
    }

    $archivedConversationIds = $this->getArchivedConversationIds($actor['archive_key']);
    if ($conversationView === 'archived') {
        if (! empty($archivedConversationIds)) {
            $conversationsQuery->whereIn('id', $archivedConversationIds);
        } else {
            $conversationsQuery->whereRaw('1 = 0');
        }
    } elseif (! empty($archivedConversationIds)) {
        $conversationsQuery->whereNotIn('id', $archivedConversationIds);
    }

    $conversations = $conversationsQuery->get();

    $announcementQuery = DigitalMarketingUpload::query()->latest();
    $archivedAnnouncementIds = DigitalMarketingUpload::getArchivedIds();

    if ($announcementView === 'archived') {
        if (! empty($archivedAnnouncementIds)) {
            $announcementQuery->whereIn('id', $archivedAnnouncementIds);
        } else {
            $announcementQuery->whereRaw('1 = 0');
        }
    } else {
        $announcementQuery->notArchived();
    }

    $digitalMarketing = $announcementQuery->get();

    return view('communication.index', compact(
        'conversations',
        'digitalMarketing',
        'conversationView',
        'announcementView'
    ));
}

    public function archiveConversation($conversationId)
    {
        $actor = $this->resolveActor();

        $conversation = Conversation::findOrFail($conversationId);
        if ($actor['type'] === 'admin' && (int) $conversation->admin_id !== (int) $actor['id']) {
            return redirect()->back()
                ->with('error', 'Unauthorized conversation archive action.')
                ->with('flash_timeout', 3000);
        }

        if ($actor['type'] === 'franchisee' && (int) $conversation->franchisee_id !== (int) $actor['id']) {
            return redirect()->back()
                ->with('error', 'Unauthorized conversation archive action.')
                ->with('flash_timeout', 3000);
        }

        $map = $this->getArchivedConversationMap();
        $key = $actor['archive_key'];
        $existing = $map[$key] ?? [];
        $existing = array_values(array_unique(array_map('intval', $existing)));

        if (! in_array((int) $conversationId, $existing, true)) {
            $existing[] = (int) $conversationId;
            $map[$key] = array_values(array_unique($existing));
            Storage::disk('local')->put('archived_conversations.json', json_encode($map));
        }

        return redirect()->route('communication.index')
            ->with('success', 'Conversation archived successfully!')
            ->with('flash_timeout', 3000);
    }

    public function restoreConversation($conversationId)
    {
        $actor = $this->resolveActor();

        $conversation = Conversation::findOrFail($conversationId);
        if ($actor['type'] === 'admin' && (int) $conversation->admin_id !== (int) $actor['id']) {
            return redirect()->back()
                ->with('error', 'Unauthorized conversation restore action.')
                ->with('flash_timeout', 3000);
        }

        if ($actor['type'] === 'franchisee' && (int) $conversation->franchisee_id !== (int) $actor['id']) {
            return redirect()->back()
                ->with('error', 'Unauthorized conversation restore action.')
                ->with('flash_timeout', 3000);
        }

        $map = $this->getArchivedConversationMap();
        $key = $actor['archive_key'];
        $existing = $map[$key] ?? [];
        $existing = array_values(array_unique(array_map('intval', $existing)));
        $existing = array_values(array_filter($existing, fn ($id) => (int) $id !== (int) $conversationId));
        $map[$key] = $existing;

        Storage::disk('local')->put('archived_conversations.json', json_encode($map));

        return redirect()->route('communication.index', ['conversation_view' => 'archived'])
            ->with('success', 'Conversation restored successfully!')
            ->with('flash_timeout', 3000);
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
            return redirect()->back()
                ->with('error', 'Unauthorized')
                ->with('flash_timeout', 3000);
        }

        $partnerId = $request->input('partner_id');
        if (!$partnerId) {
            return redirect()->back()
                ->with('error', 'Partner is required')
                ->with('flash_timeout', 3000);
        }

        if ($guard === 'admin') {
            // partner must be a franchisee
            $partner = \App\Models\Franchisee::find($partnerId);
            if (!$partner) return redirect()->back()
                ->with('error', 'Franchisee not found')
                ->with('flash_timeout', 3000);

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
            if (!$partner) return redirect()->back()
                ->with('error', 'Franchisor not found')
                ->with('flash_timeout', 3000);

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

        // If AJAX, return the chat view directly for modal
        if ($request->ajax() || $request->wantsJson()) {
            $actor = $this->resolveActor();
            $guard = $actor['type'];
            $currentUser = auth()->guard($guard)->user();
            $currentUserId = $currentUser ? $currentUser->getKey() : null;
            $currentUserType = $guard;
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
            return response()->view('communication.chat', [
                'conversation' => $conversation,
                'messages' => $messages,
                'currentUser' => $currentUser,
                'currentUserId' => $currentUserId,
                'currentUserType' => $currentUserType,
            ]);
        }
        return redirect('/communication/' . $conversation->id);
    }

    public function chatList()
    {
        $actor = $this->resolveActor();
        $conversationView = request()->query('conversation_view', 'active') === 'archived' ? 'archived' : 'active';
        $announcementView = request()->query('announcement_view', 'active') === 'archived' ? 'archived' : 'active';

        if ($actor['type'] === 'admin') {
            $conversationsQuery = Conversation::with(['admin', 'franchisee'])
                ->where('admin_id', $actor['id']);
        } else {
            $conversationsQuery = Conversation::with(['admin', 'franchisee'])
                ->where('franchisee_id', $actor['id']);
        }

        $archivedConversationIds = $this->getArchivedConversationIds($actor['archive_key']);
        if ($conversationView === 'archived') {
            if (! empty($archivedConversationIds)) {
                $conversationsQuery->whereIn('id', $archivedConversationIds);
            } else {
                $conversationsQuery->whereRaw('1 = 0');
            }
        } elseif (! empty($archivedConversationIds)) {
            $conversationsQuery->whereNotIn('id', $archivedConversationIds);
        }

        $conversations = $conversationsQuery->get();

        return view('communication._chat-list', compact('conversations', 'conversationView', 'announcementView'));
    }

    protected function resolveActor(): array
    {
        if (auth()->guard('admin')->check()) {
            $id = (int) auth()->guard('admin')->id();
            return ['type' => 'admin', 'id' => $id, 'archive_key' => 'admin:' . $id];
        }

        if (auth()->guard('franchisor_staff')->check()) {
            $staff = auth()->guard('franchisor_staff')->user();
            $id = (int) ($staff->admin_id ?? 0);
            return ['type' => 'admin', 'id' => $id, 'archive_key' => 'admin:' . $id];
        }

        if (auth()->guard('franchisee')->check()) {
            $id = (int) auth()->guard('franchisee')->id();
            return ['type' => 'franchisee', 'id' => $id, 'archive_key' => 'franchisee:' . $id];
        }

        if (auth()->guard('franchisee_staff')->check()) {
            $staff = auth()->guard('franchisee_staff')->user();
            $id = (int) ($staff->franchisee_id ?? 0);
            return ['type' => 'franchisee', 'id' => $id, 'archive_key' => 'franchisee:' . $id];
        }

        abort(403, 'Unauthorized');
    }

    protected function getArchivedConversationMap(): array
    {
        if (! Storage::disk('local')->exists('archived_conversations.json')) {
            return [];
        }

        $raw = Storage::disk('local')->get('archived_conversations.json');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    protected function getArchivedConversationIds(string $archiveKey): array
    {
        $map = $this->getArchivedConversationMap();
        $ids = $map[$archiveKey] ?? [];

        return is_array($ids) ? array_values(array_unique(array_map('intval', $ids))) : [];
    }
}

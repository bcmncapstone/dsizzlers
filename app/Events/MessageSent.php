<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Use private channel
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastWith()
    {
        // Prepare sender name
        $senderName = 'User';
        if ($this->message->sender_type === 'admin') {
            $sender = \App\Models\Admin::find($this->message->sender_id);
            $senderName = $sender
                ? trim(($sender->admin_fname ?? '') . ' ' . ($sender->admin_lname ?? '')) ?: 'Admin'
                : 'Admin';
        } elseif ($this->message->sender_type === 'franchisee') {
            $sender = \App\Models\Franchisee::find($this->message->sender_id);
            $senderName = $sender ? ($sender->franchisee_name ?: 'Franchisee') : 'Franchisee';
        }

        // Return full message with all needed data
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->message->sender_id,
                'sender_type' => $this->message->sender_type,
                'sender_name' => $senderName,
                'message_text' => $this->message->message_text,
                'file_path' => $this->message->file_path,
                'file_name' => $this->message->file_name,
                'file_type' => $this->message->file_type,
                'created_at' => $this->message->created_at->toIso8601String(),
                'formatted_time' => $this->message->created_at->format('h:i A')
            ]
        ];
    }
}

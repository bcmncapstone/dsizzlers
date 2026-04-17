<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $senderName;

    public function __construct(Message $message, ?string $senderName = null)
    {
        $this->message = $message;
        $this->senderName = $senderName;
    }

    public function broadcastOn()
    {
        // Use private channel
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastWith()
    {
        $senderName = $this->senderName ?: 'User';

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message_text',
        'file_path',
        'file_name',
        'file_type'
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    protected $appends = ['formatted_time'];

    public function getFormattedTimeAttribute()
    {
        return $this->created_at->format('h:i A');
    }
}


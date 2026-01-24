<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['admin_id', 'franchisee_id'];

    protected $casts = [
        'admin_id' => 'integer',
        'franchisee_id' => 'integer',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function franchisee()
    {
        return $this->belongsTo(Franchisee::class);
    }
}


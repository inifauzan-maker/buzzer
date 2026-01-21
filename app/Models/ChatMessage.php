<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $fillable = [
        'chat_thread_id',
        'sender_id',
        'body',
        'read_at',
    ];

    public function thread()
    {
        return $this->belongsTo(ChatThread::class, 'chat_thread_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

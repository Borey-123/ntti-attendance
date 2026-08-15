<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = [
        'teacher_id',
        'chat_id',
        'username',
        'message',
        'is_incoming',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}

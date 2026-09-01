<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'name_kh',
        'department',
        'email',
        'phone',
        'photo',
        'position',
        'status',
        'telegram_chat_id',
        'portal_pin',
        'face_descriptor',
    ];

    protected $hidden = [
        'portal_pin',
    ];

    public function rfidCard(): HasOne
    {
        return $this->hasOne(RfidCard::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)->whereDate('date', today());
    }
}

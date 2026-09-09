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
        'is_geofence_exempt',
        'portal_pin',
        'face_descriptor',
        'base_salary',
        'position_rank',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bakong_account_id',
    ];

    protected $casts = [
        'is_geofence_exempt' => 'boolean',
        'base_salary' => 'decimal:2',
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

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}

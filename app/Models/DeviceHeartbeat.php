<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceHeartbeat extends Model
{
    protected $fillable = [
        'device_code',
        'device_name',
        'ip_address',
        'last_ping_at',
        'status',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
    ];
}

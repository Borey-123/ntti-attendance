<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'teacher_id', 'date', 'rfid_uid',
        'morning_in', 'morning_out', 'morning_status',
        'afternoon_in', 'afternoon_out', 'afternoon_status',
        'evening_in', 'evening_out', 'evening_status',
        'manual_note', 'latitude', 'longitude', 'checkin_method'
    ];

    protected $casts = ['date' => 'date'];
    
    protected $appends = ['type'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getTypeAttribute()
    {
        $times = [
            ['in' => true,  'val' => $this->morning_in],
            ['in' => false, 'val' => $this->morning_out],
            ['in' => true,  'val' => $this->afternoon_in],
            ['in' => false, 'val' => $this->afternoon_out],
            ['in' => true,  'val' => $this->evening_in],
            ['in' => false, 'val' => $this->evening_out],
        ];
        
        $valid = array_filter($times, fn($t) => !empty($t['val']));
        if (empty($valid)) return 'check-in';
        
        usort($valid, fn($a, $b) => strcmp($b['val'], $a['val']));
        return $valid[0]['in'] ? 'check-in' : 'check-out';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'admin_note',
        'attachment_path'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}

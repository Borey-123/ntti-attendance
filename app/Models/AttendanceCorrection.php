<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $table = 'attendance_corrections';

    protected $fillable = [
        'teacher_id',
        'date',
        'shift', // 'morning' or 'afternoon'
        'reason',
        'status', // 'pending', 'approved', 'rejected'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
?>

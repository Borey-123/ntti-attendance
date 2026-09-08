<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'academic_year_id', 'name', 'name_kh', 'type',
        'start_date', 'end_date', 'is_attendance_required', 'color', 'notes',
    ];

    protected $casts = [
        'start_date'             => 'date',
        'end_date'               => 'date',
        'is_attendance_required' => 'boolean',
    ];

    // Type → color map defaults
    const TYPE_COLORS = [
        'semester'       => '#00d4a0',
        'term'           => '#6366f1',
        'exam'           => '#ef4444',
        'holiday_break'  => '#f59e0b',
        'other'          => '#94a3b8',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    // ─── Scopes ──────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', today())->where('end_date', '>=', today());
    }

    public function scopeRequiresAttendance($query)
    {
        return $query->where('is_attendance_required', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ─── Accessors ───────────────────────────────────────────
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getWorkingDaysAttribute(): int
    {
        $days = 0;
        $current = $this->start_date->copy();
        while ($current->lte($this->end_date)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }
        return $days;
    }

    public function getProgressPercentAttribute(): float
    {
        if (today()->lt($this->start_date)) return 0;
        if (today()->gt($this->end_date)) return 100;
        $elapsed = $this->start_date->diffInDays(today());
        $total   = $this->start_date->diffInDays($this->end_date);
        return $total > 0 ? round(($elapsed / $total) * 100, 1) : 0;
    }

    public function getIsActiveAttribute(): bool
    {
        return today()->between($this->start_date, $this->end_date);
    }

    public function getStatusLabelAttribute(): string
    {
        if (today()->lt($this->start_date)) return 'upcoming';
        if (today()->gt($this->end_date))   return 'completed';
        return 'active';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_kh ?: $this->name;
    }

    public function getDefaultColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? '#94a3b8';
    }
}

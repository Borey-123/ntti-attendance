<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
        'name', 'name_kh', 'start_date', 'end_date',
        'is_current', 'status', 'notes',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'is_current'  => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function periods(): HasMany
    {
        return $this->hasMany(AcademicPeriod::class)->orderBy('start_date');
    }

    // ─── Scopes ──────────────────────────────────────────────
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─── Static Helpers ──────────────────────────────────────
    /**
     * Get the currently active academic year.
     */
    public static function getCurrent(): ?self
    {
        return static::where('is_current', true)->with('periods')->first()
            ?? static::where('status', 'active')->with('periods')->latest()->first();
    }

    /**
     * Set a year as current (unset all others first).
     */
    public static function setAsCurrent(int $id): void
    {
        static::query()->update(['is_current' => false]);
        static::where('id', $id)->update(['is_current' => true, 'status' => 'active']);
    }

    // ─── Accessors ───────────────────────────────────────────
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_kh ?: $this->name;
    }
}

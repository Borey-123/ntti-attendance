<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'teacher_id', 'academic_period_id', 'month',
        'working_days', 'present_days', 'absent_days',
        'late_count', 'late_minutes_total', 'approved_leave_days', 'overtime_hours',
        'base_salary', 'daily_rate', 'gross_salary',
        'late_deduction', 'absent_deduction', 'bonus', 'overtime_pay', 'net_salary',
        'status', 'notes', 'approved_by', 'approved_at', 'paid_at',
    ];

    protected $casts = [
        'month'        => 'date',
        'approved_at'  => 'datetime',
        'paid_at'      => 'datetime',
        'base_salary'  => 'decimal:2',
        'daily_rate'   => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'late_deduction'   => 'decimal:2',
        'absent_deduction' => 'decimal:2',
        'bonus'            => 'decimal:2',
        'overtime_pay'     => 'decimal:2',
        'net_salary'       => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Scopes ──────────────────────────────────────────────
    public function scopeMonth($query, string $month)
    {
        return $query->where('month', $month);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)    { return $query->where('status', 'draft'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePaid($query)     { return $query->where('status', 'paid'); }

    // ─── Accessors ───────────────────────────────────────────
    public function getAttendanceRateAttribute(): float
    {
        if ($this->working_days <= 0) return 0;
        $effectiveDays = $this->present_days + $this->approved_leave_days;
        return round(($effectiveDays / $this->working_days) * 100, 1);
    }

    public function getMonthLabelAttribute(): string
    {
        return $this->month->translatedFormat('F Y');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'    => '#f59e0b',
            'approved' => '#6366f1',
            'paid'     => '#10b981',
            default    => '#94a3b8',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'draft'    => 'ph-pencil',
            'approved' => 'ph-check-circle',
            'paid'     => 'ph-money',
            default    => 'ph-circle',
        };
    }

    // ─── Business Logic ──────────────────────────────────────
    /**
     * Calculate payroll from attendance data.
     */
    public static function calculate(Teacher $teacher, string $month): array
    {
        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end   = \Carbon\Carbon::parse($month)->endOfMonth();

        // Count working days (Mon-Fri), excluding holidays
        $workingDays = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) $workingDays++;
            $current->addDay();
        }

        // Get attendance records for the month
        $attendances = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        $presentDays    = 0;
        $lateMins       = 0;
        $lateCount      = 0;
        $absentDays     = 0;

        foreach ($attendances as $att) {
            $hasCheckin = $att->morning_in || $att->afternoon_in || $att->evening_in;
            if ($hasCheckin) {
                $presentDays++;
                // Calculate late minutes (if morning_in after 08:00)
                if ($att->morning_in) {
                    $graceTime = \Carbon\Carbon::parse($att->date->format('Y-m-d') . ' 08:05:00');
                    $actualIn  = \Carbon\Carbon::parse($att->date->format('Y-m-d') . ' ' . $att->morning_in);
                    if ($actualIn->gt($graceTime)) {
                        $mins = $graceTime->diffInMinutes($actualIn);
                        $lateMins += $mins;
                        $lateCount++;
                    }
                }
            }
        }

        // Days with no attendance record = absent (on working days)
        $attendedDates = $attendances->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $currentDay = $start->copy();
        $absentDays = 0;
        while ($currentDay->lte($end)) {
            if (!$currentDay->isWeekend() && !in_array($currentDay->format('Y-m-d'), $attendedDates)) {
                $absentDays++;
            }
            $currentDay->addDay();
        }

        // Approved leaves
        $approvedLeaves = LeaveRequest::where('teacher_id', $teacher->id)
            ->where('status', 'approved')
            ->whereBetween('start_date', [$start, $end])
            ->get()
            ->sum(fn($lr) => $lr->start_date->diffInDays($lr->end_date) + 1);

        // Financial calculation
        $baseSalary      = (float) ($teacher->base_salary ?? 0);
        $dailyRate       = $workingDays > 0 ? $baseSalary / $workingDays : 0;
        $lateDeductPerMin = (float) PayrollSetting::getValue('late_deduction_per_minute', 0.50);
        $absentRate      = (float) PayrollSetting::getValue('absent_deduction_rate', 1.0);
        $leaveIsPaid     = (bool)  PayrollSetting::getValue('approved_leave_is_paid', true);

        $effectivePresentDays = $presentDays + ($leaveIsPaid ? $approvedLeaves : 0);
        $grossSalary     = min($effectivePresentDays, $workingDays) * $dailyRate;
        $lateDeduction   = $lateMins * $lateDeductPerMin;
        $absentDeduction = $absentDays * $dailyRate * $absentRate;
        $netSalary       = max(0, $grossSalary - $lateDeduction - $absentDeduction);

        return [
            'working_days'        => $workingDays,
            'present_days'        => $presentDays,
            'absent_days'         => $absentDays,
            'late_count'          => $lateCount,
            'late_minutes_total'  => $lateMins,
            'approved_leave_days' => (int) $approvedLeaves,
            'overtime_hours'      => 0,
            'base_salary'         => $baseSalary,
            'daily_rate'          => round($dailyRate, 4),
            'gross_salary'        => round($grossSalary, 2),
            'late_deduction'      => round($lateDeduction, 2),
            'absent_deduction'    => round($absentDeduction, 2),
            'bonus'               => 0,
            'overtime_pay'        => 0,
            'net_salary'          => round($netSalary, 2),
        ];
    }
}

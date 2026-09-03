<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use Carbon\Carbon;

class AnalyticsEngine
{
    /**
     * Calculate teacher punctuality score (0 - 100%) and worked hours / overtime for a given date range.
     */
    public static function calculateTeacherScorecard(int $teacherId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

        $records = Attendance::where('teacher_id', $teacherId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totalDays = $records->count();
        if ($totalDays === 0) {
            return [
                'score' => 100,
                'grade' => 'A+',
                'present_days' => 0,
                'late_days' => 0,
                'absent_days' => 0,
                'total_worked_hours' => 0.0,
                'scheduled_hours' => 0.0,
                'overtime_hours' => 0.0,
                'shortage_hours' => 0.0,
            ];
        }

        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $totalWorkedMinutes = 0;

        foreach ($records as $record) {
            // Count status
            $isLate = ($record->morning_status === 'late' || $record->afternoon_status === 'late' || $record->evening_status === 'late');
            $isAbsent = ($record->morning_status === 'absent' && $record->afternoon_status === 'absent' && $record->evening_status === 'absent');

            if ($isAbsent) {
                $absentDays++;
            } elseif ($isLate) {
                $lateDays++;
                $presentDays++;
            } else {
                $presentDays++;
            }

            // Calculate worked minutes for morning, afternoon, evening shifts
            $shifts = [
                ['in' => $record->morning_in, 'out' => $record->morning_out],
                ['in' => $record->afternoon_in, 'out' => $record->afternoon_out],
                ['in' => $record->evening_in, 'out' => $record->evening_out],
            ];

            foreach ($shifts as $shift) {
                if ($shift['in'] && $shift['out']) {
                    $inTime = Carbon::parse($shift['in']);
                    $outTime = Carbon::parse($shift['out']);
                    if ($outTime->greaterThan($inTime)) {
                        $totalWorkedMinutes += $inTime->diffInMinutes($outTime);
                    }
                }
            }
        }

        // Calculate Punctuality Score Index formula:
        // Score = 100 - ( (LateDays * 5) + (AbsentDays * 20) ) / TotalDays
        $penalty = (($lateDays * 5) + ($absentDays * 20));
        $score = max(0, min(100, round(100 - ($penalty / max(1, $totalDays)))));

        $grade = 'A+';
        if ($score < 60) $grade = 'F';
        elseif ($score < 70) $grade = 'D';
        elseif ($score < 80) $grade = 'C';
        elseif ($score < 90) $grade = 'B';
        elseif ($score < 95) $grade = 'A';

        $workedHours = round($totalWorkedMinutes / 60, 2);
        // Standard expected hours per working day (e.g. 7 hours/day)
        $expectedHours = round($presentDays * 7, 2);
        $overtimeHours = max(0, round($workedHours - $expectedHours, 2));
        $shortageHours = max(0, round($expectedHours - $workedHours, 2));

        return [
            'score' => $score,
            'grade' => $grade,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'total_worked_hours' => $workedHours,
            'scheduled_hours' => $expectedHours,
            'overtime_hours' => $overtimeHours,
            'shortage_hours' => $shortageHours,
        ];
    }
}

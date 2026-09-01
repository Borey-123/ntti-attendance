<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\SecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        if ($request->filled('month')) {
            SecurityLog::record(
                'View Analytics',
                'Analytics Module',
                "Generated analytics performance report for month: {$month}"
            );
        }

        // 1. Monthly Heatmap Data (count of check-ins per day)
        $dailyCounts = Attendance::whereBetween('date', [$startDate, $endDate])
            ->select('date', DB::raw('count(*) as count'))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // 2. Department Punctuality Ranking
        $departments = Department::all()->map(function ($dept) use ($startDate, $endDate) {
            $teacherIds = Teacher::where('department', $dept->name)->pluck('id');
            $totalScans = Attendance::whereIn('teacher_id', $teacherIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->count();
            
            $onTimeScans = Attendance::whereIn('teacher_id', $teacherIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->where(function($q) {
                    $q->where('morning_status', 'present')
                      ->orWhere('afternoon_status', 'present')
                      ->orWhere('evening_status', 'present');
                })
                ->count();

            $punctualityRate = $totalScans > 0 ? round(($onTimeScans / $totalScans) * 100, 1) : 100;

            return [
                'name' => $dept->name,
                'name_kh' => $dept->name_kh,
                'total_teachers' => count($teacherIds),
                'total_scans' => $totalScans,
                'punctuality_rate' => $punctualityRate
            ];
        })->sortByDesc('punctuality_rate');

        // 3. Top Punctual Teachers
        $topTeachers = Teacher::withCount(['attendance' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        }])->orderByDesc('attendance_count')->take(5)->get();

        return view('analytics.index', compact('month', 'startDate', 'endDate', 'dailyCounts', 'departments', 'topTeachers'));
    }
}

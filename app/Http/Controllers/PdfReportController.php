<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\Department;
use App\Services\AnalyticsEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PdfReportController extends Controller
{
    public function generate(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $departmentId = $request->input('department_id');
        $teacherId = $request->input('teacher_id');

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $query = Attendance::with(['teacher.department'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($departmentId) {
            $query->whereHas('teacher', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        $attendances = $query->orderBy('date', 'asc')->get();

        $teachers = Teacher::with('department')->get();
        $departments = Department::all();

        $selectedTeacher = $teacherId ? Teacher::find($teacherId) : null;
        $selectedDept = $departmentId ? Department::find($departmentId) : null;

        $scorecard = null;
        if ($selectedTeacher) {
            $scorecard = AnalyticsEngine::calculateTeacherScorecard($selectedTeacher->id, $startDate->toDateString(), $endDate->toDateString());
        }

        return view('reports.pdf_template', compact(
            'attendances', 'month', 'teachers', 'departments',
            'selectedTeacher', 'selectedDept', 'scorecard', 'startDate', 'endDate'
        ));
    }
}

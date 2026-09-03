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

        $query = Attendance::with(['teacher'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($departmentId) {
            $query->whereHas('teacher', function ($q) use ($departmentId) {
                if (is_numeric($departmentId)) {
                    $deptObj = Department::find($departmentId);
                    if ($deptObj) {
                        $q->where('department', $deptObj->name);
                    }
                } else {
                    $q->where('department', $departmentId);
                }
            });
        }

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        $attendances = $query->orderBy('date', 'asc')->get();

        $teachers = Teacher::all();
        $departments = Department::all();

        $selectedTeacher = $teacherId ? Teacher::find($teacherId) : null;
        $selectedDept = $departmentId ? (is_numeric($departmentId) ? Department::find($departmentId) : Department::where('name', $departmentId)->orWhere('name_kh', $departmentId)->first()) : null;

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

<?php

namespace App\Http\Controllers;

use App\Models\TeacherSchedule;
use App\Models\Teacher;
use App\Models\SecurityLog;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('name')->get();
        $schedules = TeacherSchedule::with('teacher')->orderBy('day_of_week')->orderBy('start_time')->get();
        return view('schedules.index', compact('teachers', 'schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required',
            'end_time' => 'required',
            'subject_name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:50',
        ]);

        $schedule = TeacherSchedule::create($request->all());
        $teacher = Teacher::find($request->teacher_id);
        $teacherName = $teacher ? $teacher->name : "ID #{$request->teacher_id}";

        SecurityLog::record(
            'Add Schedule Slot',
            $teacherName,
            "Added class: {$request->subject_name} (Room: " . ($request->room_number ?? 'N/A') . ", Day {$request->day_of_week}, {$request->start_time} - {$request->end_time})"
        );

        return back()->with('success', __('Schedule slot added successfully.'));
    }

    public function destroy($id)
    {
        $schedule = TeacherSchedule::with('teacher')->findOrFail($id);
        $teacherName = $schedule->teacher ? $schedule->teacher->name : "ID #{$schedule->teacher_id}";
        $subjectName = $schedule->subject_name;

        $schedule->delete();

        SecurityLog::record(
            'Delete Schedule Slot',
            $teacherName,
            "Deleted class slot: {$subjectName}"
        );

        return back()->with('success', __('Schedule slot deleted.'));
    }
}

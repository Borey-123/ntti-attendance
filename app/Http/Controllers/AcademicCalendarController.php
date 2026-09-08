<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    // ─── Main View ───────────────────────────────────────────
    public function index()
    {
        $years = AcademicYear::with('periods')->orderByDesc('start_date')->get();
        $current = AcademicYear::getCurrent();

        return view('academic.index', compact('years', 'current'));
    }

    // ─── Academic Years CRUD ─────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'name_kh'    => 'nullable|string|max:200',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'required|in:upcoming,active,completed',
            'notes'      => 'nullable|string',
        ]);

        $year = AcademicYear::create($data);

        if ($request->boolean('set_as_current')) {
            AcademicYear::setAsCurrent($year->id);
        }

        return response()->json(['success' => true, 'year' => $year->load('periods')]);
    }

    public function update(Request $request, $id)
    {
        $year = AcademicYear::findOrFail($id);
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'name_kh'    => 'nullable|string|max:200',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'required|in:upcoming,active,completed',
            'notes'      => 'nullable|string',
        ]);

        $year->update($data);
        return response()->json(['success' => true, 'year' => $year]);
    }

    public function destroy($id)
    {
        $year = AcademicYear::findOrFail($id);
        $year->delete();
        return response()->json(['success' => true]);
    }

    public function setCurrent($id)
    {
        AcademicYear::setAsCurrent((int) $id);
        return response()->json(['success' => true]);
    }

    // ─── Academic Periods CRUD ───────────────────────────────
    public function addPeriod(Request $request)
    {
        $data = $request->validate([
            'academic_year_id'       => 'required|exists:academic_years,id',
            'name'                   => 'required|string|max:150',
            'name_kh'                => 'nullable|string|max:200',
            'type'                   => 'required|in:semester,term,exam,holiday_break,other',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'is_attendance_required' => 'boolean',
            'color'                  => 'nullable|string|max:20',
            'notes'                  => 'nullable|string',
        ]);

        $data['is_attendance_required'] = $request->boolean('is_attendance_required', true);
        $data['color'] = $data['color'] ?? AcademicPeriod::TYPE_COLORS[$data['type']] ?? '#00d4a0';

        $period = AcademicPeriod::create($data);

        return response()->json([
            'success' => true,
            'period'  => $period->load('academicYear'),
        ]);
    }

    public function updatePeriod(Request $request, $id)
    {
        $period = AcademicPeriod::findOrFail($id);
        $data = $request->validate([
            'name'                   => 'required|string|max:150',
            'name_kh'                => 'nullable|string|max:200',
            'type'                   => 'required|in:semester,term,exam,holiday_break,other',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'is_attendance_required' => 'boolean',
            'color'                  => 'nullable|string|max:20',
            'notes'                  => 'nullable|string',
        ]);
        $data['is_attendance_required'] = $request->boolean('is_attendance_required', true);
        $period->update($data);

        return response()->json(['success' => true, 'period' => $period]);
    }

    public function destroyPeriod($id)
    {
        AcademicPeriod::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ─── JSON data for frontend ──────────────────────────────
    public function getData()
    {
        $years = AcademicYear::with('periods')->orderByDesc('start_date')->get();

        return response()->json([
            'years'   => $years,
            'current' => AcademicYear::getCurrent(),
            'active_period' => AcademicPeriod::active()->with('academicYear')->first(),
        ]);
    }
}

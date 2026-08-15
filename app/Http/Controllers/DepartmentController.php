<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\SecurityLog;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['head', 'teachers'])->withCount('teachers')->get();
        $teachers = Teacher::orderBy('name')->get();
        
        // For API responses (AJAX)
        if (request()->expectsJson() || request()->is('api-web/*')) {
            return response()->json($departments);
        }

        return view('departments.index', compact('departments', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'name_kh' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:teachers,id',
        ]);

        $department = Department::create($validated);

        SecurityLog::record('Created Department', $department->name);

        return response()->json([
            'message' => 'Department created successfully',
            'department' => $department
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'name_kh' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:teachers,id',
        ]);

        $department->update($validated);

        SecurityLog::record('Updated Department', $department->name);

        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department
        ]);
    }

    public function destroy(Department $department)
    {
        $name = $department->name;
        $department->delete();
        
        SecurityLog::record('Deleted Department', $name);
        
        return response()->json(['message' => 'Department deleted successfully']);
    }
}

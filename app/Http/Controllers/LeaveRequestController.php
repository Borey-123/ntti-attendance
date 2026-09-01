<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests for admins.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with('teacher')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->paginate(15);
        $activeStatus  = $request->input('status', 'all');
        return view('leave.index', compact('leaveRequests', 'activeStatus'));
    }

    /**
     * Store a newly created leave request from teacher portal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $leave = LeaveRequest::create([
            'teacher_id' => $request->teacher_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Leave request submitted successfully.'),
                'data' => $leave
            ]);
        }

        return back()->with('success', __('Leave request submitted successfully.'));
    }

    /**
     * Update leave request status (Approve / Reject).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500'
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Leave request status updated to :status', ['status' => ucfirst($request->status)])
        ]);
    }
}

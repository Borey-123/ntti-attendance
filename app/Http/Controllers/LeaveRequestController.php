<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use App\Models\SecurityLog;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests for admins.
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['teacher', 'substituteTeacher'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->paginate(15);
        $activeStatus  = $request->input('status', 'all');
        return view('leave.index', compact('leaveRequests', 'activeStatus'));
    }

    /**
     * Store a newly created leave request from teacher portal or admin.
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
        $leave->load('teacher');

        // Dispatch 1-tap interactive approval card to Admin Telegram
        try {
            \App\Services\TelegramService::sendAdminLeaveAlert($leave);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Telegram sendAdminLeaveAlert error: ' . $e->getMessage());
        }

        $teacher = Teacher::find($request->teacher_id);
        $teacherName = $teacher ? $teacher->name : "ID #{$request->teacher_id}";

        if (auth()->check()) {
            SecurityLog::record(
                'Create Leave Request',
                $teacherName,
                "Submitted {$request->leave_type} leave request ({$request->start_date} to {$request->end_date})"
            );
        } else {
            SecurityLog::recordPortal(
                'Create Leave Request',
                $teacherName,
                "Teacher submitted {$request->leave_type} leave request ({$request->start_date} to {$request->end_date})"
            );
        }

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

        $leave = LeaveRequest::with('teacher')->findOrFail($id);
        $leave->update([
            'status' => $request->status,
            'admin_note' => $request->admin_note
        ]);

        // Send Telegram alert to teacher
        \App\Services\TelegramService::sendLeaveNotification($leave);

        $teacherName = $leave->teacher ? $leave->teacher->name : "ID #{$leave->teacher_id}";
        $actionTitle = ucfirst($request->status) . ' Leave Request';

        SecurityLog::record(
            $actionTitle,
            $teacherName,
            "Leave request #{$id} ({$leave->leave_type}: {$leave->start_date} to {$leave->end_date}) status set to {$request->status}" . ($request->admin_note ? " - Note: {$request->admin_note}" : "")
        );

        return response()->json([
            'success' => true,
            'message' => __('Leave request status updated to :status', ['status' => ucfirst($request->status)])
        ]);
    }

    /**
     * Get conflict-free substitute teacher suggestions for a leave request.
     */
    public function getSubstituteSuggestions($id)
    {
        $leave = LeaveRequest::with(['teacher', 'substituteTeacher'])->findOrFail($id);
        $teacher = $leave->teacher;

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found.'], 404);
        }

        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);

        // Determine days of week during the leave period (1=Mon ... 7=Sun)
        $daysOfWeek = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dow = $curr->dayOfWeekIso;
            if (!in_array($dow, $daysOfWeek)) {
                $daysOfWeek[] = $dow;
            }
            $curr->addDay();
        }

        // Get absent teacher's teaching slots during those days
        $slotsToCover = TeacherSchedule::where('teacher_id', $teacher->id)
            ->whereIn('day_of_week', $daysOfWeek)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Candidates: active teachers from same department (excluding requesting teacher)
        $candidates = Teacher::where('department', $teacher->department)
            ->where('id', '!=', $teacher->id)
            ->where('status', 'active')
            ->get();

        $rankedSubstitutes = [];

        foreach ($candidates as $candidate) {
            // Get candidate's schedule on these days
            $candidateSchedules = TeacherSchedule::where('teacher_id', $candidate->id)
                ->whereIn('day_of_week', $daysOfWeek)
                ->get();

            $conflicts = [];
            foreach ($slotsToCover as $slot) {
                foreach ($candidateSchedules as $candSlot) {
                    if ($candSlot->day_of_week === $slot->day_of_week) {
                        // Overlap check
                        if ($candSlot->start_time < $slot->end_time && $candSlot->end_time > $slot->start_time) {
                            $conflicts[] = [
                                'day_of_week'  => $slot->day_of_week,
                                'slot_time'    => substr($slot->start_time, 0, 5) . ' - ' . substr($slot->end_time, 0, 5),
                                'cand_subject' => $candSlot->subject_name,
                                'cand_room'    => $candSlot->room_number,
                            ];
                        }
                    }
                }
            }

            $isConflictFree = empty($conflicts);

            $rankedSubstitutes[] = [
                'id'             => $candidate->id,
                'name'           => $candidate->name,
                'name_kh'        => $candidate->name_kh,
                'employee_id'    => $candidate->employee_id,
                'department'     => $candidate->department,
                'photo'          => $candidate->photo ? to_asset_url($candidate->photo) : null,
                'phone'          => $candidate->phone,
                'is_free'        => $isConflictFree,
                'conflict_count' => count($conflicts),
                'conflicts'      => $conflicts,
                'is_assigned'    => ($leave->substitute_teacher_id == $candidate->id),
            ];
        }

        // Sort: currently assigned first, then conflict-free first, then least conflicts
        usort($rankedSubstitutes, function ($a, $b) {
            if ($a['is_assigned'] && !$b['is_assigned']) return -1;
            if (!$a['is_assigned'] && $b['is_assigned']) return 1;
            if ($a['is_free'] && !$b['is_free']) return -1;
            if (!$a['is_free'] && $b['is_free']) return 1;
            return $a['conflict_count'] <=> $b['conflict_count'];
        });

        return response()->json([
            'success'            => true,
            'leave'              => [
                'id'         => $leave->id,
                'teacher'    => $teacher->name,
                'teacher_kh' => $teacher->name_kh,
                'department' => $teacher->department,
                'start_date' => $leave->start_date,
                'end_date'   => $leave->end_date,
                'leave_type' => ucfirst($leave->leave_type),
                'reason'     => $leave->reason,
                'substitute' => $leave->substituteTeacher ? [
                    'id'   => $leave->substituteTeacher->id,
                    'name' => $leave->substituteTeacher->name,
                ] : null,
            ],
            'slots_count'        => $slotsToCover->count(),
            'slots_to_cover'     => $slotsToCover,
            'candidates'         => $rankedSubstitutes,
        ]);
    }

    /**
     * Assign a substitute teacher to a leave request and update teaching schedules.
     */
    public function assignSubstitute(Request $request, $id)
    {
        $request->validate([
            'substitute_teacher_id' => 'required|exists:teachers,id',
        ]);

        $leave = LeaveRequest::with('teacher')->findOrFail($id);
        $substitute = Teacher::findOrFail($request->substitute_teacher_id);

        $leave->update([
            'substitute_teacher_id' => $substitute->id,
        ]);

        // Link substitute to teacher's affected schedules during this period
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        $daysOfWeek = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dow = $curr->dayOfWeekIso;
            if (!in_array($dow, $daysOfWeek)) $daysOfWeek[] = $dow;
            $curr->addDay();
        }

        TeacherSchedule::where('teacher_id', $leave->teacher_id)
            ->whereIn('day_of_week', $daysOfWeek)
            ->update(['substitute_teacher_id' => $substitute->id]);

        SecurityLog::record(
            'Assign Substitute Teacher',
            $leave->teacher->name ?? 'Teacher',
            "Assigned {$substitute->name} as substitute for Leave #{$leave->id} ({$leave->start_date} to {$leave->end_date})"
        );

        // Telegram alert to substitute teacher
        if (!empty($substitute->telegram_chat_id)) {
            $msg = "📋 *NTTI Academic Notice: Substitute Assignment*\n\n"
                 . "You have been assigned as substitute teacher for *{$leave->teacher->name}* ({$leave->teacher->department})\n"
                 . "📅 *Dates*: {$leave->start_date} to {$leave->end_date}\n"
                 . "📝 *Leave Reason*: {$leave->reason}\n\n"
                 . "Please check your schedule on the Teacher Portal.";
            TelegramService::sendMessage($substitute->telegram_chat_id, $msg);
        }

        return response()->json([
            'success'    => true,
            'message'    => __(':substitute has been assigned as the substitute teacher.', ['substitute' => $substitute->name]),
            'substitute' => $substitute,
        ]);
    }
}

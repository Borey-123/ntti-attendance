<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\PayrollSetting;
use App\Models\Teacher;
use App\Models\AcademicPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayrollController extends Controller
{
    // ─── Index: List payrolls ────────────────────────────────
    public function index(Request $request)
    {
        $month      = $request->get('month', now()->format('Y-m'));
        $department = $request->get('department');
        $status     = $request->get('status');

        $query = Payroll::with(['teacher', 'academicPeriod'])
            ->where('month', Carbon::parse($month)->startOfMonth()->format('Y-m-d'));

        if ($department) {
            $query->whereHas('teacher', fn($q) => $q->where('department', $department));
        }
        if ($status) {
            $query->where('status', $status);
        }

        $payrolls   = $query->orderBy('created_at', 'desc')->get();

        // Auto-synchronize if teacher has a configured base_salary but payroll snapshot is 0,
        // or if payroll net_salary was 0 due to previous calculation issue
        foreach ($payrolls as $p) {
            $teacherBase = (float) ($p->teacher?->base_salary ?? 0);
            if ($teacherBase > 0 && ((float)$p->base_salary == 0 || (float)$p->net_salary == 0)) {
                $calc = Payroll::calculate($p->teacher, $p->month->format('Y-m-d'));
                $p->update($calc);
                $p->refresh();
            }
        }

        $teachers   = Teacher::where('status', 'active')->orderBy('name')->get();
        $departments = Teacher::select('department')->distinct()->pluck('department');
        $settings   = PayrollSetting::getAllMap();

        $summary = [
            'total_teachers' => $payrolls->count(),
            'total_payout'   => $payrolls->sum('net_salary'),
            'avg_salary'     => $payrolls->avg('net_salary'),
            'draft_count'    => $payrolls->where('status', 'draft')->count(),
            'approved_count' => $payrolls->where('status', 'approved')->count(),
            'paid_count'     => $payrolls->where('status', 'paid')->count(),
        ];

        return view('payroll.index', compact('payrolls', 'teachers', 'departments', 'settings', 'month', 'summary'));
    }

    // ─── Generate payroll for teachers ──────────────────────
    public function generate(Request $request)
    {
        $data = $request->validate([
            'month'       => 'required|date_format:Y-m',
            'teacher_ids' => 'nullable|array',
            'teacher_ids.*' => 'exists:teachers,id',
            'period_id'   => 'nullable|exists:academic_periods,id',
        ]);

        $monthDate = Carbon::parse($data['month'] . '-01')->startOfMonth();
        $teacherIds = $data['teacher_ids'] ?? Teacher::where('status', 'active')->pluck('id')->toArray();

        $generated = 0;
        $errors    = [];

        foreach ($teacherIds as $tid) {
            try {
                $teacher = Teacher::findOrFail($tid);
                $calc    = Payroll::calculate($teacher, $monthDate->format('Y-m-d'));

                Payroll::updateOrCreate(
                    ['teacher_id' => $tid, 'month' => $monthDate->format('Y-m-d')],
                    array_merge($calc, [
                        'academic_period_id' => $data['period_id'] ?? null,
                        'status'             => 'draft',
                    ])
                );
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Teacher #{$tid}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success'   => true,
            'status'    => 'success',
            'generated' => $generated,
            'errors'    => $errors,
            'message'   => "Generated payroll for {$generated} teachers.",
        ]);
    }

    // ─── Recalculate single payroll ─────────────────────────
    public function recalculate($id)
    {
        $payroll = Payroll::with('teacher')->findOrFail($id);
        $calc    = Payroll::calculate($payroll->teacher, $payroll->month->format('Y-m-d'));
        $payroll->update($calc);

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'payroll' => $payroll,
            'message' => 'បានគណនាឡើងវិញដោយជោគជ័យ',
        ]);
    }

    // ─── Show single payroll ─────────────────────────────────
    public function show($id)
    {
        $payroll  = Payroll::with(['teacher', 'academicPeriod', 'approvedByUser'])->findOrFail($id);
        $settings = PayrollSetting::getAllMap();

        // Get attendance breakdown for the month
        $attendances = \App\Models\Attendance::where('teacher_id', $payroll->teacher_id)
            ->whereBetween('date', [
                $payroll->month->startOfMonth(),
                $payroll->month->endOfMonth(),
            ])
            ->orderBy('date')
            ->get();

        return view('payroll.show', compact('payroll', 'settings', 'attendances'));
    }

    // ─── Approve payroll ─────────────────────────────────────
    public function approve($id)
    {
        $payroll = Payroll::findOrFail($id);
        if ($payroll->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft payrolls can be approved.'], 422);
        }

        $payroll->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json(['success' => true, 'payroll' => $payroll]);
    }

    // ─── Bulk approve ────────────────────────────────────────
    public function bulkApprove(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:payrolls,id'])['ids'];
        $count = Payroll::whereIn('id', $ids)->where('status', 'draft')->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        return response()->json(['success' => true, 'count' => $count]);
    }

    // ─── Mark as Paid ────────────────────────────────────────
    public function markPaid($id)
    {
        $payroll = Payroll::findOrFail($id);
        if ($payroll->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Only approved payrolls can be marked as paid.'], 422);
        }

        $payroll->update(['status' => 'paid', 'paid_at' => now()]);
        return response()->json(['success' => true, 'payroll' => $payroll]);
    }

    // ─── PDF Payslip ─────────────────────────────────────────
    public function exportPdf($id)
    {
        $payroll     = Payroll::with(['teacher', 'academicPeriod', 'approvedByUser'])->findOrFail($id);
        $settings    = PayrollSetting::getAllMap();
        $attendances = \App\Models\Attendance::where('teacher_id', $payroll->teacher_id)
            ->whereBetween('date', [
                $payroll->month->copy()->startOfMonth(),
                $payroll->month->copy()->endOfMonth(),
            ])
            ->orderBy('date')
            ->get();

        $pdf = Pdf::loadView('payroll.pdf_slip', compact('payroll', 'settings', 'attendances'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download("payslip_{$payroll->teacher->employee_id}_{$payroll->month->format('Y_m')}.pdf");
    }

    // ─── Export CSV ──────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $payrolls = Payroll::with('teacher')
            ->where('month', Carbon::parse($month . '-01')->format('Y-m-d'))
            ->get();

        $filename = "payroll_{$month}.csv";
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payrolls) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID', 'Employee ID', 'Name', 'Department', 'Month',
                'Working Days', 'Present', 'Absent', 'Late (mins)',
                'Base Salary', 'Gross Salary', 'Late Deduction', 'Absent Deduction',
                'Bonus', 'Net Salary', 'Status'
            ]);
            foreach ($payrolls as $p) {
                fputcsv($out, [
                    $p->id,
                    $p->teacher->employee_id ?? '',
                    $p->teacher->name ?? '',
                    $p->teacher->department ?? '',
                    $p->month->format('Y-m'),
                    $p->working_days,
                    $p->present_days,
                    $p->absent_days,
                    $p->late_minutes_total,
                    $p->base_salary,
                    $p->gross_salary,
                    $p->late_deduction,
                    $p->absent_deduction,
                    $p->bonus,
                    $p->net_salary,
                    $p->status,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Settings ────────────────────────────────────────────
    public function settings()
    {
        $settings = PayrollSetting::all();
        return view('payroll.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'late_deduction_per_minute' => 'required|numeric|min:0',
            'absent_deduction_rate'     => 'required|numeric|min:0|max:3',
            'overtime_rate_per_hour'    => 'required|numeric|min:0',
            'working_days_per_month'    => 'required|integer|min:1|max:31',
            'approved_leave_is_paid'    => 'nullable|boolean',
            'currency'                  => 'required|string|max:5',
            'currency_symbol'           => 'required|string|max:5',
            'payroll_note_footer'       => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            PayrollSetting::setValue($key, $value ?? 0);
        }

        return response()->json(['success' => true]);
    }

    // ─── JSON list ───────────────────────────────────────────
    public function getData(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $payrolls = Payroll::with(['teacher'])
            ->where('month', Carbon::parse($month . '-01')->format('Y-m-d'))
            ->get();

        return response()->json($payrolls);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('Telegram Webhook Received:', $data);

        if (isset($data['message'])) {
            $this->processIncomingMessage($data['message']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function processIncomingMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text   = trim($message['text'] ?? '');

        if (!$chatId || !$text) {
            return;
        }

        // Find teacher by Telegram Chat ID
        $teacher = Teacher::where('telegram_chat_id', (string)$chatId)->first();

        $cleanText = strtolower(ltrim($text, '/'));

        // --- /start ---
        if (str_starts_with(strtolower($text), '/start') || $cleanText === 'start') {
            $parts = explode(' ', $text);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $teacherId = $parts[1];
                $t = Teacher::find($teacherId);
                if ($t) {
                    $t->update(['telegram_chat_id' => (string)$chatId]);
                    $this->sendMessage($chatId, "✅ Hello {$t->name}! Your Telegram account has been linked to the NTTI Teacher Attendance System.");
                    return;
                }
            }

            if ($teacher) {
                $this->sendMessage($chatId, $this->buildHelpText($teacher->name));
            } else {
                $this->sendMessage($chatId, "👋 Welcome to NTTI Attendance Bot!\nTo link your profile, please copy your personal link from your Teacher Portal.");
            }
            return;
        }

        if (!$teacher) {
            $this->sendMessage($chatId, "⚠️ Account not linked. Please link your Telegram account from the NTTI Teacher Portal.");
            return;
        }

        // --- Route commands ---
        switch ($cleanText) {
            case 'status':
                $this->handleStatus($chatId, $teacher);
                break;

            case 'monthly':
                $this->handleMonthly($chatId, $teacher);
                break;

            case 'week':
                $this->handleWeek($chatId, $teacher);
                break;

            case 'history':
                $this->handleHistory($chatId, $teacher);
                break;

            case 'leave':
                $this->handleLeave($chatId, $teacher);
                break;

            case 'profile':
                $this->handleProfile($chatId, $teacher);
                break;

            case 'schedule':
                $this->handleSchedule($chatId, $teacher);
                break;

            case 'help':
            default:
                $this->sendMessage($chatId, $this->buildHelpText());
                break;
        }
    }

    // =========================================================================
    // COMMAND HANDLERS
    // =========================================================================

    /**
     * /status — Today's check-in & check-out per shift.
     */
    private function handleStatus(int|string $chatId, Teacher $teacher): void
    {
        $today = Carbon::today()->toDateString();
        $att   = Attendance::where('teacher_id', $teacher->id)->where('date', $today)->first();

        if (!$att) {
            $this->sendMessage($chatId,
                "📋 *Today's Attendance Status*\n"
              . "👤 *Teacher:* {$teacher->name}\n"
              . "📅 *Date:* {$today}\n"
              . "⚠️ No attendance recorded for today yet."
            );
            return;
        }

        $fmtShift = function (?string $in, ?string $out, string $status): ?string {
            if (empty($in) && empty($out)) return null;
            $inStr  = $in  ? Carbon::parse($in)->format('h:i A')  : 'Not scanned';
            $outStr = $out ? Carbon::parse($out)->format('h:i A') : 'Still in';
            $badge  = match ($status) {
                'late'    => ' ⚠️ Late',
                'present' => ' ✅ On-time',
                default   => '',
            };
            return "{$inStr} → {$outStr}{$badge}";
        };

        $lines = [];
        if ($m = $fmtShift($att->morning_in,   $att->morning_out,   $att->morning_status   ?? '')) $lines[] = "🌅 *Morning:* {$m}";
        if ($a = $fmtShift($att->afternoon_in, $att->afternoon_out, $att->afternoon_status ?? '')) $lines[] = "☀️ *Afternoon:* {$a}";
        if ($e = $fmtShift($att->evening_in,   $att->evening_out,   $att->evening_status   ?? '')) $lines[] = "🌙 *Evening:* {$e}";

        if (empty($lines)) {
            $lines[] = "⏰ *Check-In:* Not scanned\n🚪 *Check-Out:* Not scanned";
        }

        $this->sendMessage($chatId,
            "📋 *Today's Attendance Status*\n"
          . "👤 *Teacher:* {$teacher->name}\n"
          . "📅 *Date:* {$today}\n"
          . implode("\n", $lines)
        );
    }

    /**
     * /monthly — Attendance summary for the current month.
     */
    private function handleMonthly(int|string $chatId, Teacher $teacher): void
    {
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->toDateString();
        $monthName  = $now->format('F Y');

        [$workedDays, $onTimeDays, $lateDays, $totalWorkingDays] = $this->calcAttendanceStats($teacher->id, $monthStart, $monthEnd);

        $leaveDays  = $this->calcLeaveDays($teacher->id, $monthStart, $monthEnd);
        $absentDays = max(0, $totalWorkingDays - $workedDays - $leaveDays);

        $this->sendMessage($chatId,
            "📊 *Monthly Attendance Summary*\n"
          . "👤 *Teacher:* {$teacher->name}\n"
          . "🗓️ *Month:* {$monthName}\n"
          . "─────────────────────\n"
          . "✅ *Days Worked:* {$workedDays} / {$totalWorkingDays}\n"
          . "🕐 *On-Time Days:* {$onTimeDays}\n"
          . "⚠️ *Late Days:* {$lateDays}\n"
          . "📝 *Leave Days:* {$leaveDays}\n"
          . "❌ *Absent Days:* {$absentDays}"
        );
    }

    /**
     * /week — Attendance summary for the current week (Mon–today).
     */
    private function handleWeek(int|string $chatId, Teacher $teacher): void
    {
        $now       = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd   = $now->copy()->toDateString();

        [$workedDays, $onTimeDays, $lateDays, $totalWorkingDays] = $this->calcAttendanceStats($teacher->id, $weekStart, $weekEnd);

        $leaveDays  = $this->calcLeaveDays($teacher->id, $weekStart, $weekEnd);
        $absentDays = max(0, $totalWorkingDays - $workedDays - $leaveDays);

        $this->sendMessage($chatId,
            "📅 *This Week's Summary*\n"
          . "👤 *Teacher:* {$teacher->name}\n"
          . "🗓️ *Week:* " . Carbon::parse($weekStart)->format('d M') . " – " . Carbon::parse($weekEnd)->format('d M Y') . "\n"
          . "─────────────────────\n"
          . "✅ *Days Worked:* {$workedDays} / {$totalWorkingDays}\n"
          . "🕐 *On-Time Days:* {$onTimeDays}\n"
          . "⚠️ *Late Days:* {$lateDays}\n"
          . "📝 *Leave Days:* {$leaveDays}\n"
          . "❌ *Absent Days:* {$absentDays}"
        );
    }

    /**
     * /history — Last 7 days of attendance records.
     */
    private function handleHistory(int|string $chatId, Teacher $teacher): void
    {
        $records = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [
                Carbon::today()->subDays(6)->toDateString(),
                Carbon::today()->toDateString(),
            ])
            ->orderBy('date', 'desc')
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        $msg = "🗂️ *Last 7 Days Attendance*\n"
             . "👤 *Teacher:* {$teacher->name}\n"
             . "─────────────────────\n";

        $workingDaysRaw  = \App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]');
        $workingDayAbbrs = json_decode($workingDaysRaw, true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        for ($i = 6; $i >= 0; $i--) {
            $day    = Carbon::today()->subDays($i);
            $dateStr = $day->toDateString();
            $label  = $day->format('D, d M');

            if (!in_array($day->format('D'), $workingDayAbbrs)) {
                $msg .= "📆 *{$label}* — Day off\n";
                continue;
            }

            $r = $records->get($dateStr);
            if (!$r) {
                $msg .= "📆 *{$label}* — ❌ Absent\n";
                continue;
            }

            $shifts = [];
            if (!empty($r->morning_in)) {
                $badge  = $r->morning_status === 'late' ? '⚠️' : '✅';
                $in     = Carbon::parse($r->morning_in)->format('h:i A');
                $out    = $r->morning_out ? Carbon::parse($r->morning_out)->format('h:i A') : '—';
                $shifts[] = "{$badge} AM {$in}→{$out}";
            }
            if (!empty($r->afternoon_in)) {
                $badge  = $r->afternoon_status === 'late' ? '⚠️' : '✅';
                $in     = Carbon::parse($r->afternoon_in)->format('h:i A');
                $out    = $r->afternoon_out ? Carbon::parse($r->afternoon_out)->format('h:i A') : '—';
                $shifts[] = "{$badge} PM {$in}→{$out}";
            }

            $shiftStr = !empty($shifts) ? implode(' | ', $shifts) : '⏳ No scan';
            $msg .= "📆 *{$label}* — {$shiftStr}\n";
        }

        $this->sendMessage($chatId, $msg);
    }

    /**
     * /leave — Show approved & pending leave requests.
     */
    private function handleLeave(int|string $chatId, Teacher $teacher): void
    {
        $leaves = LeaveRequest::where('teacher_id', $teacher->id)
            ->orderBy('start_date', 'desc')
            ->take(5)
            ->get();

        if ($leaves->isEmpty()) {
            $this->sendMessage($chatId,
                "📝 *Leave Requests*\n"
              . "👤 *Teacher:* {$teacher->name}\n"
              . "─────────────────────\n"
              . "No leave requests found."
            );
            return;
        }

        $msg = "📝 *Leave Requests (Latest 5)*\n"
             . "👤 *Teacher:* {$teacher->name}\n"
             . "─────────────────────\n";

        foreach ($leaves as $leave) {
            $statusIcon = match ($leave->status) {
                'approved' => '✅',
                'rejected' => '❌',
                'pending'  => '⏳',
                default    => '❓',
            };
            $start    = Carbon::parse($leave->start_date)->format('d M Y');
            $end      = Carbon::parse($leave->end_date)->format('d M Y');
            $type     = ucfirst(str_replace('_', ' ', $leave->leave_type ?? 'Leave'));
            $msg .= "{$statusIcon} *{$type}*\n"
                  . "   📅 {$start} → {$end}\n"
                  . "   Status: " . ucfirst($leave->status) . "\n\n";
        }

        $this->sendMessage($chatId, $msg);
    }

    /**
     * /profile — Show teacher profile information.
     */
    private function handleProfile(int|string $chatId, Teacher $teacher): void
    {
        $dept     = $teacher->department ?? '—';
        $pos      = $teacher->position   ?? '—';
        $empId    = $teacher->employee_id ?? '—';
        $status   = ucfirst($teacher->status ?? 'active');
        $joined   = $teacher->created_at
            ? Carbon::parse($teacher->created_at)->format('d M Y')
            : '—';

        $this->sendMessage($chatId,
            "👤 *Teacher Profile*\n"
          . "─────────────────────\n"
          . "🪪 *Name:* {$teacher->name}\n"
          . "🆔 *Employee ID:* {$empId}\n"
          . "🏫 *Department:* {$dept}\n"
          . "💼 *Position:* {$pos}\n"
          . "🟢 *Status:* {$status}\n"
          . "📅 *Joined:* {$joined}"
        );
    }

    /**
     * /schedule — Today's class schedule.
     */
    private function handleSchedule(int|string $chatId, Teacher $teacher): void
    {
        $dayOfWeek = (int) Carbon::now()->format('N'); // 1=Mon, 7=Sun
        $schedules = TeacherSchedule::where('teacher_id', $teacher->id)
            ->where('day_of_week', $dayOfWeek)
            ->orderBy('start_time')
            ->get();

        if ($schedules->count() > 0) {
            $msg = "📅 *Today's Teaching Schedule*\n";
            foreach ($schedules as $s) {
                $msg .= "• *{$s->subject_name}*: {$s->start_time} - {$s->end_time} (Room: {$s->room_number})\n";
            }
        } else {
            $msg = "📅 *Today's Teaching Schedule*\nNo classes scheduled for today!";
        }
        $this->sendMessage($chatId, $msg);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Calculate attendance stats for a date range.
     * Returns [$workedDays, $onTimeDays, $lateDays, $totalWorkingDays]
     */
    private function calcAttendanceStats(int $teacherId, string $from, string $to): array
    {
        $records = Attendance::where('teacher_id', $teacherId)
            ->whereBetween('date', [$from, $to])
            ->get();

        $workingDaysRaw  = \App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]');
        $workingDayAbbrs = json_decode($workingDaysRaw, true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        $totalWorkingDays = 0;
        $cursor = Carbon::parse($from);
        $end    = Carbon::parse($to);
        while ($cursor->lte($end)) {
            if (in_array($cursor->format('D'), $workingDayAbbrs)) $totalWorkingDays++;
            $cursor->addDay();
        }

        $workedDays = $records->filter(fn($r) =>
            !empty($r->morning_in) || !empty($r->afternoon_in) || !empty($r->evening_in)
        )->count();

        $lateDays = $records->filter(fn($r) =>
            $r->morning_status === 'late' || $r->afternoon_status === 'late' || $r->evening_status === 'late'
        )->count();

        $onTimeDays = $records->filter(function ($r) {
            $hasPresent = in_array($r->morning_status, ['present']) ||
                          in_array($r->afternoon_status, ['present']) ||
                          in_array($r->evening_status, ['present']);
            $hasLate    = in_array($r->morning_status, ['late']) ||
                          in_array($r->afternoon_status, ['late']) ||
                          in_array($r->evening_status, ['late']);
            return $hasPresent && !$hasLate;
        })->count();

        return [$workedDays, $onTimeDays, $lateDays, $totalWorkingDays];
    }

    /**
     * Count approved leave days overlapping a date range.
     */
    private function calcLeaveDays(int $teacherId, string $from, string $to): int
    {
        return (int) LeaveRequest::where('teacher_id', $teacherId)
            ->where('status', 'approved')
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])
                  ->orWhereBetween('end_date',  [$from, $to]);
            })
            ->get()
            ->sum(function ($leave) use ($from, $to) {
                $start = Carbon::parse(max($leave->start_date, $from));
                $end   = Carbon::parse(min($leave->end_date,   $to));
                return max(0, $start->diffInDays($end) + 1);
            });
    }

    /**
     * Build the help / welcome text.
     */
    private function buildHelpText(string $name = ''): string
    {
        $greeting = $name ? "👋 Welcome back, *{$name}*!\n\n" : '';
        return $greeting
             . "ℹ️ *Available Commands*\n"
             . "─────────────────────\n"
             . "• `/status`   — Today's check-in/out\n"
             . "• `/history`  — Last 7 days attendance\n"
             . "• `/week`     — This week's summary\n"
             . "• `/monthly`  — This month's summary\n"
             . "• `/leave`    — Your leave requests\n"
             . "• `/profile`  — Your teacher profile\n"
             . "• `/schedule` — Today's class schedule\n"
             . "• `/help`     — Show this help";
    }

    private function sendMessage($chatId, $text)
    {
        $botToken = \App\Models\Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            Log::error('Telegram Webhook: Bot Token not found in settings or env.');
            return;
        }

        $res = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ]);

        Log::info('Telegram Webhook Send Message Result:', [
            'chat_id'  => $chatId,
            'text'     => $text,
            'status'   => $res->status(),
            'response' => $res->json(),
        ]);
    }
}

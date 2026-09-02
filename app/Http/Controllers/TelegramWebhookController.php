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
        $text = trim($message['text'] ?? '');

        if (!$chatId || !$text) {
            return;
        }

        // Find teacher by Telegram Chat ID
        $teacher = Teacher::where('telegram_chat_id', (string)$chatId)->first();

        $cleanText = strtolower(ltrim($text, '/'));

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
                $this->sendMessage($chatId, "👋 Welcome back, {$teacher->name}!\n\nAvailable commands:\n• `/status` - Check today's check-in/out status\n• `/monthly` - View this month's attendance summary\n• `/schedule` - View today's class schedule\n• `/help` - Command help");
            } else {
                $this->sendMessage($chatId, "👋 Welcome to NTTI Attendance Bot!\nTo link your profile, please copy your personal link from your Teacher Portal.");
            }
            return;
        }

        if (!$teacher) {
            $this->sendMessage($chatId, "⚠️ Account not linked. Please link your Telegram account from the NTTI Teacher Portal.");
            return;
        }

        // Handle commands for linked teachers
        switch ($cleanText) {
            case 'status':
                $this->handleStatus($chatId, $teacher);
                break;

            case 'monthly':
                $this->handleMonthly($chatId, $teacher);
                break;

            case 'schedule':
                $dayOfWeek = date('N'); // 1 (Mon) to 7 (Sun)
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
                break;

            case 'help':
            default:
                $msg = "ℹ️ *Available Commands*\n"
                     . "• `/status` - Check today's check-in/out times\n"
                     . "• `/monthly` - View this month's attendance summary\n"
                     . "• `/schedule` - View today's class schedule\n"
                     . "• `/help` - Show command help";
                $this->sendMessage($chatId, $msg);
                break;
        }
    }

    /**
     * Handle /status command — shows today's check-in & check-out times per shift.
     */
    private function handleStatus(int|string $chatId, Teacher $teacher): void
    {
        $today = Carbon::today()->toDateString();
        $att = Attendance::where('teacher_id', $teacher->id)->where('date', $today)->first();

        if (!$att) {
            $msg = "📋 *Today's Attendance Status*\n"
                 . "👤 *Teacher:* {$teacher->name}\n"
                 . "📅 *Date:* {$today}\n"
                 . "⚠️ No attendance recorded for today yet.";
            $this->sendMessage($chatId, $msg);
            return;
        }

        // Format a single scan line, returns null if both in & out are empty
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
        if ($m = $fmtShift($att->morning_in, $att->morning_out, $att->morning_status ?? '')) {
            $lines[] = "🌅 *Morning:* {$m}";
        }
        if ($a = $fmtShift($att->afternoon_in, $att->afternoon_out, $att->afternoon_status ?? '')) {
            $lines[] = "☀️ *Afternoon:* {$a}";
        }
        if ($e = $fmtShift($att->evening_in, $att->evening_out, $att->evening_status ?? '')) {
            $lines[] = "🌙 *Evening:* {$e}";
        }

        if (empty($lines)) {
            $lines[] = "⏰ *Check-In:* Not scanned\n🚪 *Check-Out:* Not scanned";
        }

        $msg = "📋 *Today's Attendance Status*\n"
             . "👤 *Teacher:* {$teacher->name}\n"
             . "📅 *Date:* {$today}\n"
             . implode("\n", $lines);

        $this->sendMessage($chatId, $msg);
    }

    /**
     * Handle /monthly command — shows monthly attendance summary for the current month.
     * Includes: total worked days, on-time days, late days, leave days, and absent days.
     */
    private function handleMonthly(int|string $chatId, Teacher $teacher): void
    {
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->toDateString(); // Up to today

        $monthName = $now->format('F Y');

        // Fetch all attendance records for this month
        $records = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        // Count working days (Mon–Sat) from start of month to today
        $workingDaysRaw = \App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]');
        $workingDayAbbrs = json_decode($workingDaysRaw, true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];
        $totalWorkingDays = 0;
        $cursor = Carbon::parse($monthStart);
        $end    = Carbon::parse($monthEnd);
        while ($cursor->lte($end)) {
            if (in_array($cursor->format('D'), $workingDayAbbrs)) {
                $totalWorkingDays++;
            }
            $cursor->addDay();
        }

        // Count days actually worked (have at least one check-in)
        $workedDays = $records->filter(function ($r) {
            return !empty($r->morning_in) || !empty($r->afternoon_in) || !empty($r->evening_in);
        })->count();

        // Count on-time days (at least one shift with status = 'present', no 'late')
        $onTimeDays = $records->filter(function ($r) {
            $hasPresent = in_array($r->morning_status, ['present']) ||
                          in_array($r->afternoon_status, ['present']) ||
                          in_array($r->evening_status, ['present']);
            $hasLate    = in_array($r->morning_status, ['late']) ||
                          in_array($r->afternoon_status, ['late']) ||
                          in_array($r->evening_status, ['late']);
            return $hasPresent && !$hasLate;
        })->count();

        // Count late days (at least one shift marked late)
        $lateDays = $records->filter(function ($r) {
            return $r->morning_status === 'late'
                || $r->afternoon_status === 'late'
                || $r->evening_status === 'late';
        })->count();

        // Count approved leave days this month (from leave_requests table)
        $leaveDays = LeaveRequest::where('teacher_id', $teacher->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('start_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('end_date',   [$monthStart, $monthEnd]);
            })
            ->get()
            ->sum(function ($leave) use ($monthStart, $monthEnd) {
                $start = Carbon::parse(max($leave->start_date, $monthStart));
                $end   = Carbon::parse(min($leave->end_date,   $monthEnd));
                return max(0, $start->diffInDays($end) + 1);
            });

        // Absent days = working days - worked days - leave days
        $absentDays = max(0, $totalWorkingDays - $workedDays - $leaveDays);

        $msg = "📊 *Monthly Attendance Summary*\n"
             . "👤 *Teacher:* {$teacher->name}\n"
             . "🗓️ *Month:* {$monthName}\n"
             . "─────────────────────\n"
             . "✅ *Days Worked:* {$workedDays} / {$totalWorkingDays}\n"
             . "🕐 *On-Time Days:* {$onTimeDays}\n"
             . "⚠️ *Late Days:* {$lateDays}\n"
             . "📝 *Leave Days:* {$leaveDays}\n"
             . "❌ *Absent Days:* {$absentDays}";

        $this->sendMessage($chatId, $msg);
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
            'parse_mode' => 'Markdown'
        ]);

        Log::info('Telegram Webhook Send Message Result:', [
            'chat_id'  => $chatId,
            'text'     => $text,
            'status'   => $res->status(),
            'response' => $res->json()
        ]);
    }
}

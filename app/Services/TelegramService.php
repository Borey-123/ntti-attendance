<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramService
{
    /**
     * Send a raw markdown message to a specific Telegram Chat ID.
     */
    public static function sendMessage(int|string $chatId, string $text, array $replyMarkup = []): bool
    {
        $botToken = Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) {
            Log::error('TelegramService: Bot token not configured.');
            return false;
        }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
            Log::info('TelegramService sendMessage result:', ['chat_id' => $chatId, 'status' => $res->status(), 'response' => $res->json()]);
            return $res->successful() && ($res->json('ok') === true);
        } catch (\Throwable $e) {
            Log::error("TelegramService Exception sending to {$chatId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send message to a Teacher model if telegram_chat_id is present.
     */
    public static function sendToTeacher(Teacher $teacher, string $text, array $replyMarkup = []): bool
    {
        if (empty($teacher->telegram_chat_id)) {
            return false;
        }
        return self::sendMessage($teacher->telegram_chat_id, $text, $replyMarkup);
    }

    /**
     * Generate & send monthly summary to a teacher (used by monthly broadcast & /monthly command).
     */
    public static function sendMonthlyReport(Teacher $teacher, ?Carbon $dateObj = null): bool
    {
        if (empty($teacher->telegram_chat_id)) {
            return false;
        }

        $now        = $dateObj ? $dateObj->copy() : Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateString();
        $monthName  = $now->format('F Y');

        $records = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        $abbrs = json_decode(Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]'), true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        $totalWorkingDays = 0;
        $c = Carbon::parse($monthStart);
        $targetEnd = $now->isCurrentMonth() ? Carbon::today() : Carbon::parse($monthEnd);
        while ($c->lte($targetEnd)) {
            if (in_array($c->format('D'), $abbrs)) {
                $totalWorkingDays++;
            }
            $c->addDay();
        }

        $workedDays = $records->filter(fn($r) => !empty($r->morning_in) || !empty($r->afternoon_in) || !empty($r->evening_in))->count();
        $lateDays   = $records->filter(fn($r) => $r->morning_status === 'late' || $r->afternoon_status === 'late' || $r->evening_status === 'late')->count();
        $onTimeDays = $records->filter(function ($r) {
            $p = in_array($r->morning_status, ['present']) || in_array($r->afternoon_status, ['present']) || in_array($r->evening_status, ['present']);
            $l = in_array($r->morning_status, ['late'])    || in_array($r->afternoon_status, ['late'])    || in_array($r->evening_status, ['late']);
            return $p && !$l;
        })->count();

        $leaveDays = (int) LeaveRequest::where('teacher_id', $teacher->id)
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereBetween('start_date', [$monthStart, $monthEnd])->orWhereBetween('end_date', [$monthStart, $monthEnd]))
            ->get()->sum(function ($l) use ($monthStart, $monthEnd) {
                $s = Carbon::parse(max($l->start_date, $monthStart));
                $e = Carbon::parse(min($l->end_date,   $monthEnd));
                return max(0, $s->diffInDays($e) + 1);
            });

        $absentDays = max(0, $totalWorkingDays - $workedDays - $leaveDays);
        $pct        = $totalWorkingDays > 0 ? round(($workedDays / $totalWorkingDays) * 100) : 0;
        $bar        = str_repeat('█', (int)($pct / 10)) . str_repeat('░', 10 - (int)($pct / 10));

        $msg = "📊 *Monthly Attendance Summary*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n"
             . "🗓️ *{$monthName}*\n\n"
             . "✅  Worked    →  *{$workedDays} / {$totalWorkingDays} days*\n"
             . "🕐  On-Time  →  *{$onTimeDays} days*\n"
             . "⚠️  Late       →  *{$lateDays} days*\n"
             . "📝  Leave     →  *{$leaveDays} days*\n"
             . "❌  Absent   →  *{$absentDays} days*\n\n"
             . "`{$bar}` *{$pct}%*";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🏠 Back to Menu', 'callback_data' => 'menu']],
            ],
        ];

        return self::sendMessage($teacher->telegram_chat_id, $msg, $keyboard);
    }

    /**
     * Send morning check-in reminder.
     */
    public static function sendMorningReminder(Teacher $teacher): bool
    {
        $today = Carbon::today()->format('l, d M Y');
        $msg   = "🌅 *Good Morning, {$teacher->name}!*\n"
               . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
               . "📅 {$today}\n\n"
               . "⏰ Don't forget to scan your RFID card or check in at the station for your morning shift today!";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📋 Check Status', 'callback_data' => 'status']],
            ],
        ];

        return self::sendToTeacher($teacher, $msg, $keyboard);
    }

    /**
     * Send absent alert at end of day.
     */
    public static function sendAbsentAlert(Teacher $teacher): bool
    {
        $today = Carbon::today()->format('l, d M Y');
        $msg   = "🚨 *End-of-Day Attendance Notice*\n"
               . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
               . "👤 {$teacher->name}\n"
               . "📅 {$today}\n\n"
               . "❌ No attendance scan recorded for you today. If you were on duty or submitted a leave request, please contact Administration.";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📝 Leave Requests', 'callback_data' => 'leave']],
            ],
        ];

        return self::sendToTeacher($teacher, $msg, $keyboard);
    }

    /**
     * Send late warning if late 3+ times.
     */
    public static function sendLateWarning(Teacher $teacher, int $lateCount): bool
    {
        $monthName = Carbon::now()->format('F Y');
        $msg       = "⚠️ *Punctuality Advisory*\n"
                   . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
                   . "👤 {$teacher->name}\n"
                   . "🗓️ {$monthName}\n\n"
                   . "You have been marked late *{$lateCount} times* this month.\n"
                   . "Please ensure timely arrival to maintain institution standards.";

        return self::sendToTeacher($teacher, $msg);
    }

    /**
     * Send leave request decision notification to teacher.
     */
    public static function sendLeaveNotification(LeaveRequest $leave): bool
    {
        if (!$leave->teacher || empty($leave->teacher->telegram_chat_id)) {
            return false;
        }

        $icon      = $leave->status === 'approved' ? '✅' : '❌';
        $statusStr = strtoupper($leave->status);
        $start     = Carbon::parse($leave->start_date)->format('d M Y');
        $end       = Carbon::parse($leave->end_date)->format('d M Y');
        $type      = ucfirst(str_replace('_', ' ', $leave->leave_type ?? 'Leave'));

        $msg = "{$icon} *Leave Request Update*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$leave->teacher->name}\n"
             . "📋 *Type:* {$type}\n"
             . "📅 *Dates:* {$start} → {$end}\n"
             . "📌 *Status:* *{$statusStr}*\n";

        if (!empty($leave->admin_note)) {
            $msg .= "💬 *Note:* {$leave->admin_note}\n";
        }

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📝 View All Requests', 'callback_data' => 'leave']],
            ],
        ];

        return self::sendMessage($leave->teacher->telegram_chat_id, $msg, $keyboard);
    }
}

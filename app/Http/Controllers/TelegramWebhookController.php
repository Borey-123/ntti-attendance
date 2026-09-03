<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
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

        if (isset($data['callback_query'])) {
            $this->processCallbackQuery($data['callback_query']);
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // MESSAGE HANDLER
    // =========================================================================

    public function processIncomingMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text   = trim($message['text'] ?? '');

        if (!$chatId || !$text) return;

        $cleanText = strtolower(ltrim($text, '/'));
        $parts     = explode(' ', $text);
        $param     = isset($parts[1]) ? trim($parts[1]) : '';

        // ---------------------------------------------------------------------
        // 1. LINK TOKEN DISCOVERY & DEEP LINKING (/start link_TOKEN or /start admin_ID)
        // ---------------------------------------------------------------------
        if (str_starts_with(strtolower($text), '/start') || $cleanText === 'start') {
            if ($param) {
                // One-Time Secure Signed Link Token: /start link_TOKEN
                if (str_starts_with($param, 'link_')) {
                    $token   = str_replace('link_', '', $param);
                    $adminId = \Illuminate\Support\Facades\Cache::pull("tg_link_token_{$token}");

                    if ($adminId) {
                        $admin = \App\Models\User::find($adminId);
                        if ($admin) {
                            $admin->update(['telegram_chat_id' => (string)$chatId]);

                            $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                            $admin->two_factor_code = $code;
                            $admin->two_factor_expires_at = now()->addMinutes(10);
                            $admin->save();

                            $this->sendMessage($chatId,
                                "🔒 *Admin Account Linked Successfully!*\n\n"
                              . "Welcome, *{$admin->name}*!\n"
                              . "Your Telegram is now connected for 2FA verification.\n\n"
                              . "🔑 *Your 2FA Login OTP Code:* `{$code}`\n"
                              . "⏰ _Code expires in 10 minutes._"
                            );
                            return;
                        }
                    }
                }

                // Fallback Admin ID Link: /start admin_4
                if (str_starts_with($param, 'admin_')) {
                    $adminId = str_replace('admin_', '', $param);
                    $admin   = \App\Models\User::find($adminId);
                    if ($admin) {
                        $admin->update(['telegram_chat_id' => (string)$chatId]);

                        $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                        $admin->two_factor_code = $code;
                        $admin->two_factor_expires_at = now()->addMinutes(10);
                        $admin->save();

                        $this->sendMessage($chatId,
                            "🔒 *Admin Account Linked Successfully!*\n\n"
                          . "Welcome, *{$admin->name}*!\n"
                          . "Your Telegram is now connected for 2FA verification.\n\n"
                          . "🔑 *Your 2FA Login OTP Code:* `{$code}`\n"
                          . "⏰ _Code expires in 10 minutes._"
                        );
                        return;
                    }
                }

                // Teacher Deep Link: /start 12
                if (is_numeric($param)) {
                    $t = Teacher::find($param);
                    if ($t) {
                        $t->update(['telegram_chat_id' => (string)$chatId]);
                        $this->sendMessage($chatId,
                            "✅ *Account Linked Successfully!*\n\n"
                          . "Welcome, *{$t->name}*!\n"
                          . "You are now connected to the NTTI Attendance System.",
                            $this->mainMenuKeyboard()
                        );
                        return;
                    }
                }
            }
        }

        // ---------------------------------------------------------------------
        // 2. CHECK IF USER IS AN ADMIN (User Model)
        // ---------------------------------------------------------------------
        $adminUser = \App\Models\User::where('telegram_chat_id', (string)$chatId)->first();

        // If email entered directly (e.g. rinbory02@gmail.com)
        if (!$adminUser && filter_var($text, FILTER_VALIDATE_EMAIL)) {
            $adminUser = \App\Models\User::where('email', strtolower($text))->first();
            if ($adminUser) {
                $adminUser->update(['telegram_chat_id' => (string)$chatId]);
            }
        }

        if ($adminUser) {
            // Generate & send OTP code whenever Admin messages the bot
            $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $adminUser->two_factor_code = $code;
            $adminUser->two_factor_expires_at = now()->addMinutes(10);
            $adminUser->save();

            $this->sendMessage($chatId,
                "🔒 *NTTI Security Verification*\n\n"
              . "Hello *{$adminUser->name}*,\n"
              . "Your 2FA Login OTP Code is: `{$code}`\n\n"
              . "⏰ _Code expires in 10 minutes._"
            );
            return;
        }

        // ---------------------------------------------------------------------
        // 3. FALLBACK TO TEACHER MODEL
        // ---------------------------------------------------------------------
        $teacher = Teacher::where('telegram_chat_id', (string)$chatId)->first();

        if (!$teacher) {
            $this->sendMessage($chatId,
                "⚠️ *Account Not Linked*\n\n"
              . "Please link your Telegram from the NTTI Teacher Portal."
            );
            return;
        }

        // Check if awaiting leave request input
        $awaitingState = Cache::get("tg_state_{$chatId}");
        if ($awaitingState) {
            $this->handleLeaveRequestState($chatId, $teacher, $text, $awaitingState);
            return;
        }

        switch ($cleanText) {
            case 'status':   $this->handleStatus($chatId, $teacher);   break;
            case 'history':  $this->handleHistory($chatId, $teacher);  break;
            case 'week':     $this->handleWeek($chatId, $teacher);     break;
            case 'monthly':  $this->handleMonthly($chatId, $teacher);  break;
            case 'leave':    $this->handleLeave($chatId, $teacher);    break;
            case 'profile':  $this->handleProfile($chatId, $teacher);  break;
            case 'schedule': $this->handleSchedule($chatId, $teacher); break;
            case 'request':  $this->startLeaveRequest($chatId, $teacher); break;
            case 'menu':
            case 'help':
            default:
                $this->sendMessage($chatId,
                    "📱 *NTTI Attendance Bot*\n_Choose an option below:_",
                    $this->mainMenuKeyboard()
                );
                break;
        }
    }

    // =========================================================================
    // CALLBACK QUERY HANDLER (inline button taps)
    // =========================================================================

    public function processCallbackQuery(array $callbackQuery)
    {
        $chatId   = $callbackQuery['message']['chat']['id'] ?? null;
        $msgId    = $callbackQuery['message']['message_id'] ?? null;
        $queryId  = $callbackQuery['id'];
        $data     = $callbackQuery['data'] ?? '';

        if (!$chatId) return;

        // Acknowledge the tap immediately (removes loading spinner)
        $this->answerCallbackQuery($queryId);

        $teacher = Teacher::where('telegram_chat_id', (string)$chatId)->first();
        if (!$teacher) {
            $this->answerCallbackQuery($queryId, '⚠️ Account not linked.');
            return;
        }

        // Leave type selection
        if (str_starts_with($data, 'lr_type_')) {
            $type = str_replace('lr_type_', '', $data);
            Cache::put("tg_state_{$chatId}", ['step' => 'await_dates', 'leave_type' => $type], now()->addMinutes(10));
            $typeLabel = ucfirst(str_replace('_', ' ', $type));
            $this->sendMessage($chatId,
                "📨 *New Leave Request*\n"
              . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
              . "📋 *Type:* {$typeLabel}\n\n"
              . "*Step 2 of 2* — Send the date range:\n\n"
              . "`YYYY-MM-DD to YYYY-MM-DD`\n\n"
              . "_Example:_ `2026-09-05 to 2026-09-07`\n"
              . "_Single day:_ `2026-09-05`"
            );
            return;
        }

        switch ($data) {
            case 'status':   $this->handleStatus($chatId, $teacher);   break;
            case 'history':  $this->handleHistory($chatId, $teacher);  break;
            case 'week':     $this->handleWeek($chatId, $teacher);     break;
            case 'monthly':  $this->handleMonthly($chatId, $teacher);  break;
            case 'leave':    $this->handleLeave($chatId, $teacher);    break;
            case 'profile':  $this->handleProfile($chatId, $teacher);  break;
            case 'schedule': $this->handleSchedule($chatId, $teacher); break;
            case 'request':  $this->startLeaveRequest($chatId, $teacher); break;
            case 'menu':
                $this->sendMessage($chatId,
                    "📱 *NTTI Attendance Bot*\n_Choose an option below:_",
                    $this->mainMenuKeyboard()
                );
                break;
        }
    }

    // =========================================================================
    // INLINE KEYBOARD LAYOUTS
    // =========================================================================

    private function mainMenuKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '📋 Today\'s Status',  'callback_data' => 'status'],
                    ['text' => '🗂️ Last 7 Days',       'callback_data' => 'history'],
                ],
                [
                    ['text' => '📅 This Week',         'callback_data' => 'week'],
                    ['text' => '📊 This Month',        'callback_data' => 'monthly'],
                ],
                [
                    ['text' => '📝 Leave Requests',   'callback_data' => 'leave'],
                    ['text' => '📨 Request Leave',    'callback_data' => 'request'],
                ],
                [
                    ['text' => '👤 My Profile',        'callback_data' => 'profile'],
                    ['text' => '🗓️ My Schedule',       'callback_data' => 'schedule'],
                ],
            ],
        ];
    }

    private function backToMenuKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🏠 Back to Menu', 'callback_data' => 'menu'],
                ],
            ],
        ];
    }

    // =========================================================================
    // COMMAND HANDLERS
    // =========================================================================

    private function handleStatus(int|string $chatId, Teacher $teacher): void
    {
        $today = Carbon::today()->toDateString();
        $att   = Attendance::where('teacher_id', $teacher->id)->where('date', $today)->first();

        if (!$att) {
            $msg = "📋 *Today's Attendance Status*\n"
                 . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
                 . "👤 {$teacher->name}\n"
                 . "📅 {$today}\n\n"
                 . "⚠️ _No attendance recorded today._";
            $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
            return;
        }

        $fmtShift = function (?string $in, ?string $out, string $status): ?string {
            if (empty($in) && empty($out)) return null;
            $inStr  = $in  ? Carbon::parse($in)->format('h:i A')  : 'Not scanned';
            $outStr = $out ? Carbon::parse($out)->format('h:i A') : 'Still in';
            $badge  = match ($status) { 'late' => ' ⚠️', 'present' => ' ✅', default => '' };
            return "{$inStr} → {$outStr}{$badge}";
        };

        $lines = [];
        if ($m = $fmtShift($att->morning_in,   $att->morning_out,   $att->morning_status   ?? '')) $lines[] = "🌅 *Morning:*   {$m}";
        if ($a = $fmtShift($att->afternoon_in, $att->afternoon_out, $att->afternoon_status ?? '')) $lines[] = "☀️ *Afternoon:* {$a}";
        if ($e = $fmtShift($att->evening_in,   $att->evening_out,   $att->evening_status   ?? '')) $lines[] = "🌙 *Evening:*   {$e}";

        if (empty($lines)) $lines[] = "⏰ Not scanned yet";

        $msg = "📋 *Today's Attendance Status*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n"
             . "📅 {$today}\n\n"
             . implode("\n", $lines);

        $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
    }

    private function handleHistory(int|string $chatId, Teacher $teacher): void
    {
        $records = Attendance::where('teacher_id', $teacher->id)
            ->whereBetween('date', [Carbon::today()->subDays(6)->toDateString(), Carbon::today()->toDateString()])
            ->orderBy('date', 'desc')->get()->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        $workingDayAbbrs = json_decode(\App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]'), true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        $msg = "🗂️ *Last 7 Days Attendance*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n\n";

        for ($i = 6; $i >= 0; $i--) {
            $day     = Carbon::today()->subDays($i);
            $dateStr = $day->toDateString();
            $label   = $day->format('D d/m');

            if (!in_array($day->format('D'), $workingDayAbbrs)) {
                $msg .= "🔵 *{$label}* — Day off\n"; continue;
            }
            $r = $records->get($dateStr);
            if (!$r) { $msg .= "❌ *{$label}* — Absent\n"; continue; }

            $shifts = [];
            if (!empty($r->morning_in)) {
                $b = $r->morning_status === 'late' ? '⚠️' : '✅';
                $shifts[] = "{$b} " . Carbon::parse($r->morning_in)->format('h:i A');
            }
            if (!empty($r->afternoon_in)) {
                $b = $r->afternoon_status === 'late' ? '⚠️' : '✅';
                $shifts[] = "{$b} " . Carbon::parse($r->afternoon_in)->format('h:i A');
            }
            $msg .= "📆 *{$label}* — " . (!empty($shifts) ? implode(' | ', $shifts) : '⏳ No scan') . "\n";
        }

        $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
    }

    private function handleWeek(int|string $chatId, Teacher $teacher): void
    {
        $now       = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd   = $now->copy()->toDateString();

        [$worked, $onTime, $late, $total] = $this->calcAttendanceStats($teacher->id, $weekStart, $weekEnd);
        $leave   = $this->calcLeaveDays($teacher->id, $weekStart, $weekEnd);
        $absent  = max(0, $total - $worked - $leave);

        $msg = "📅 *This Week's Summary*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n"
             . "🗓️ " . Carbon::parse($weekStart)->format('d M') . " – " . Carbon::parse($weekEnd)->format('d M Y') . "\n\n"
             . "✅  Worked    →  *{$worked} / {$total} days*\n"
             . "🕐  On-Time  →  *{$onTime} days*\n"
             . "⚠️  Late       →  *{$late} days*\n"
             . "📝  Leave     →  *{$leave} days*\n"
             . "❌  Absent   →  *{$absent} days*";

        $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
    }

    private function handleMonthly(int|string $chatId, Teacher $teacher): void
    {
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd   = $now->copy()->toDateString();

        [$worked, $onTime, $late, $total] = $this->calcAttendanceStats($teacher->id, $monthStart, $monthEnd);
        $leave  = $this->calcLeaveDays($teacher->id, $monthStart, $monthEnd);
        $absent = max(0, $total - $worked - $leave);
        $pct    = $total > 0 ? round(($worked / $total) * 100) : 0;

        $bar = str_repeat('█', (int)($pct / 10)) . str_repeat('░', 10 - (int)($pct / 10));

        $msg = "📊 *Monthly Attendance Summary*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n"
             . "🗓️ " . $now->format('F Y') . "\n\n"
             . "✅  Worked    →  *{$worked} / {$total} days*\n"
             . "🕐  On-Time  →  *{$onTime} days*\n"
             . "⚠️  Late       →  *{$late} days*\n"
             . "📝  Leave     →  *{$leave} days*\n"
             . "❌  Absent   →  *{$absent} days*\n\n"
             . "`{$bar}` *{$pct}%*";

        $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
    }

    private function handleLeave(int|string $chatId, Teacher $teacher): void
    {
        $leaves = LeaveRequest::where('teacher_id', $teacher->id)
            ->orderBy('start_date', 'desc')->take(5)->get();

        $msg = "📝 *Leave Requests*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n\n";

        if ($leaves->isEmpty()) {
            $msg .= "_No leave requests found._";
        } else {
            foreach ($leaves as $leave) {
                $icon  = match ($leave->status) { 'approved' => '✅', 'rejected' => '❌', default => '⏳' };
                $start = Carbon::parse($leave->start_date)->format('d M Y');
                $end   = Carbon::parse($leave->end_date)->format('d M Y');
                $type  = ucfirst(str_replace('_', ' ', $leave->leave_type ?? 'Leave'));
                $msg  .= "{$icon} *{$type}*\n   📅 {$start} → {$end}\n   _" . ucfirst($leave->status) . "_\n\n";
            }
        }

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📨 New Leave Request', 'callback_data' => 'request']],
                [['text' => '🏠 Back to Menu',      'callback_data' => 'menu']],
            ],
        ];

        $this->sendMessage($chatId, $msg, $keyboard);
    }

    private function handleProfile(int|string $chatId, Teacher $teacher): void
    {
        $dept   = $teacher->department ?? '—';
        $pos    = $teacher->position   ?? '—';
        $empId  = $teacher->employee_id ?? '—';
        $status = ucfirst($teacher->status ?? 'active');

        $msg = "👤 *Teacher Profile*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "🪪  *Name:*        {$teacher->name}\n"
             . "🆔  *Employee ID:* {$empId}\n"
             . "🏫  *Department:*  {$dept}\n"
             . "💼  *Position:*    {$pos}\n"
             . "🟢  *Status:*      {$status}";

        $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
    }

    private function handleSchedule(int|string $chatId, Teacher $teacher): void
    {
        $dayOfWeek = (int) Carbon::now()->format('N');
        $schedules = TeacherSchedule::where('teacher_id', $teacher->id)
            ->where('day_of_week', $dayOfWeek)->orderBy('start_time')->get();

        $dayName = Carbon::now()->format('l, d M Y');
        $msg = "🗓️ *Today's Teaching Schedule*\n"
             . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
             . "👤 {$teacher->name}\n"
             . "📅 {$dayName}\n\n";

        if ($schedules->count() > 0) {
            foreach ($schedules as $s) {
                $msg .= "📚 *{$s->subject_name}*\n"
                      . "   🕐 {$s->start_time} – {$s->end_time}\n"
                      . "   🚪 Room: {$s->room_number}\n\n";
            }
        } else {
            $msg .= "_No classes scheduled for today._";
        }

        $this->sendMessage($chatId, $msg, $this->backToMenuKeyboard());
    }

    // =========================================================================
    // LEAVE REQUEST FLOW
    // =========================================================================

    private function startLeaveRequest(int|string $chatId, Teacher $teacher): void
    {
        Cache::put("tg_state_{$chatId}", ['step' => 'type'], now()->addMinutes(10));

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🤒 Sick Leave',     'callback_data' => 'lr_type_sick'],
                    ['text' => '👤 Personal Leave', 'callback_data' => 'lr_type_personal'],
                ],
                [
                    ['text' => '📋 Other',          'callback_data' => 'lr_type_other'],
                    ['text' => '❌ Cancel',          'callback_data' => 'menu'],
                ],
            ],
        ];

        $this->sendMessage($chatId,
            "📨 *New Leave Request*\n"
          . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
          . "*Step 1 of 2* — Select leave type:",
            $keyboard
        );
    }

    private function handleLeaveRequestState(int|string $chatId, Teacher $teacher, string $text, array $state): void
    {
        // Handle type selection from callback (lr_type_*)
        if ($state['step'] === 'await_dates') {
            // Parse "YYYY-MM-DD to YYYY-MM-DD" or "YYYY-MM-DD"
            $text = trim($text);
            $parts = preg_split('/\s+to\s+/i', $text);
            $start = $parts[0] ?? null;
            $end   = $parts[1] ?? $start;

            if (!$start || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
                $this->sendMessage($chatId,
                    "⚠️ *Invalid format.*\n\nPlease send dates like:\n`2026-09-05 to 2026-09-07`\nor for a single day:\n`2026-09-05`"
                );
                return;
            }

            try {
                LeaveRequest::create([
                    'teacher_id' => $teacher->id,
                    'leave_type' => $state['leave_type'],
                    'start_date' => $start,
                    'end_date'   => $end,
                    'reason'     => 'Submitted via Telegram',
                    'status'     => 'pending',
                ]);

                Cache::forget("tg_state_{$chatId}");

                $typeLabel = ucfirst(str_replace('_', ' ', $state['leave_type']));
                $this->sendMessage($chatId,
                    "✅ *Leave Request Submitted!*\n"
                  . "┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄┄\n"
                  . "📋 *Type:*  {$typeLabel}\n"
                  . "📅 *From:*  {$start}\n"
                  . "📅 *To:*    {$end}\n"
                  . "⏳ *Status:* Pending approval\n\n"
                  . "_Your admin will review the request._",
                    $this->backToMenuKeyboard()
                );
            } catch (\Throwable $e) {
                Log::error('Telegram Leave Request Error: ' . $e->getMessage());
                Cache::forget("tg_state_{$chatId}");
                $this->sendMessage($chatId, "❌ Failed to submit. Please try again.", $this->backToMenuKeyboard());
            }
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function calcAttendanceStats(int $teacherId, string $from, string $to): array
    {
        $records = Attendance::where('teacher_id', $teacherId)->whereBetween('date', [$from, $to])->get();
        $abbrs   = json_decode(\App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]'), true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        $total = 0;
        $c = Carbon::parse($from);
        while ($c->lte(Carbon::parse($to))) { if (in_array($c->format('D'), $abbrs)) $total++; $c->addDay(); }

        $worked  = $records->filter(fn($r) => !empty($r->morning_in) || !empty($r->afternoon_in) || !empty($r->evening_in))->count();
        $late    = $records->filter(fn($r) => $r->morning_status === 'late' || $r->afternoon_status === 'late' || $r->evening_status === 'late')->count();
        $onTime  = $records->filter(function ($r) {
            $p = in_array($r->morning_status, ['present']) || in_array($r->afternoon_status, ['present']) || in_array($r->evening_status, ['present']);
            $l = in_array($r->morning_status, ['late'])    || in_array($r->afternoon_status, ['late'])    || in_array($r->evening_status, ['late']);
            return $p && !$l;
        })->count();

        return [$worked, $onTime, $late, $total];
    }

    private function calcLeaveDays(int $teacherId, string $from, string $to): int
    {
        return (int) LeaveRequest::where('teacher_id', $teacherId)->where('status', 'approved')
            ->where(fn($q) => $q->whereBetween('start_date', [$from, $to])->orWhereBetween('end_date', [$from, $to]))
            ->get()->sum(function ($l) use ($from, $to) {
                $s = Carbon::parse(max($l->start_date, $from));
                $e = Carbon::parse(min($l->end_date,   $to));
                return max(0, $s->diffInDays($e) + 1);
            });
    }

    // =========================================================================
    // TELEGRAM API
    // =========================================================================

    private function sendMessage($chatId, string $text, array $replyMarkup = [])
    {
        $botToken = \App\Models\Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) { Log::error('Telegram: Bot Token not found.'); return; }

        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $res = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
        Log::info('Telegram sendMessage:', ['status' => $res->status(), 'response' => $res->json()]);
    }

    private function answerCallbackQuery(string $queryId, string $text = '')
    {
        $botToken = \App\Models\Setting::getValue('telegram_bot_token') ?: env('TELEGRAM_BOT_TOKEN');
        if (!$botToken) return;

        Http::post("https://api.telegram.org/bot{$botToken}/answerCallbackQuery", [
            'callback_query_id' => $queryId,
            'text'              => $text,
        ]);
    }
}

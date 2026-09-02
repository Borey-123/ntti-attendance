<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\TelegramService;
use Carbon\Carbon;

class TelegramAbsentAlertCron extends Command
{
    protected $signature = 'telegram:absent-alert';
    protected $description = 'Send absent notification at end of day to unscanned teachers';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $dayName = Carbon::today()->format('D');

        $workingDaysRaw = \App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]');
        $workingDayAbbrs = json_decode($workingDaysRaw, true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        if (!in_array($dayName, $workingDayAbbrs)) {
            $this->info("Today ({$dayName}) is not a working day. Skipping absent alert.");
            return 0;
        }

        $teachers = Teacher::whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '')->get();

        $count = 0;
        foreach ($teachers as $teacher) {
            $hasScanned = Attendance::where('teacher_id', $teacher->id)
                ->where('date', $today)
                ->where(function($q) {
                    $q->whereNotNull('morning_in')->orWhereNotNull('afternoon_in')->orWhereNotNull('evening_in');
                })
                ->exists();

            $onLeave = LeaveRequest::where('teacher_id', $teacher->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->exists();

            if (!$hasScanned && !$onLeave) {
                if (TelegramService::sendAbsentAlert($teacher)) {
                    $count++;
                }
            }
        }

        $this->info("Sent absent notices to {$count} teachers.");
        return 0;
    }
}

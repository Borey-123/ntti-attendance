<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Services\TelegramService;
use Carbon\Carbon;

class TelegramMorningReminderCron extends Command
{
    protected $signature = 'telegram:morning-reminder';
    protected $description = 'Send morning check-in reminder to teachers who have not scanned yet today';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $dayName = Carbon::today()->format('D');

        $workingDaysRaw = \App\Models\Setting::getValue('working_days', '["Mon","Tue","Wed","Thu","Fri","Sat"]');
        $workingDayAbbrs = json_decode($workingDaysRaw, true) ?: ['Mon','Tue','Wed','Thu','Fri','Sat'];

        if (!in_array($dayName, $workingDayAbbrs)) {
            $this->info("Today ({$dayName}) is not a working day. Skipping morning reminder.");
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

            if (!$hasScanned) {
                if (TelegramService::sendMorningReminder($teacher)) {
                    $count++;
                }
            }
        }

        $this->info("Sent morning check-in reminders to {$count} teachers.");
        return 0;
    }
}

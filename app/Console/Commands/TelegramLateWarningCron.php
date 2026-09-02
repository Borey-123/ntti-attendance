<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Models\Attendance;
use App\Services\TelegramService;
use Carbon\Carbon;

class TelegramLateWarningCron extends Command
{
    protected $signature = 'telegram:late-warning {--threshold=3 : Minimum late count to trigger warning}';
    protected $description = 'Send Telegram late warning to teachers with 3+ late scans this month';

    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd   = Carbon::now()->toDateString();

        $teachers = Teacher::whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '')->get();

        $count = 0;
        foreach ($teachers as $teacher) {
            $lateCount = Attendance::where('teacher_id', $teacher->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->where(function($q) {
                    $q->where('morning_status', 'late')
                      ->orWhere('afternoon_status', 'late')
                      ->orWhere('evening_status', 'late');
                })
                ->count();

            if ($lateCount >= $threshold) {
                if (TelegramService::sendLateWarning($teacher, $lateCount)) {
                    $count++;
                }
            }
        }

        $this->info("Sent late warning to {$count} teachers with {$threshold}+ late scans.");
        return 0;
    }
}

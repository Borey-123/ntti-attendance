<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use App\Services\TelegramService;
use Carbon\Carbon;

class TelegramMonthlyReportCron extends Command
{
    protected $signature = 'telegram:monthly-report {--month= : Target month YYYY-MM (defaults to previous month if run on 1st, or current month)}';
    protected $description = 'Send monthly attendance report to all linked teachers';

    public function handle()
    {
        $this->info('Starting automated monthly attendance report dispatch...');

        $monthOption = $this->option('month');
        if ($monthOption) {
            $targetDate = Carbon::parse($monthOption . '-01');
        } else {
            // If today is 1st of month, default to previous month summary
            $today = Carbon::today();
            if ($today->day === 1) {
                $targetDate = $today->copy()->subMonth();
            } else {
                $targetDate = $today;
            }
        }

        $teachers = Teacher::whereNotNull('telegram_chat_id')->where('telegram_chat_id', '!=', '')->get();

        $count = 0;
        foreach ($teachers as $teacher) {
            $sent = TelegramService::sendMonthlyReport($teacher, $targetDate);
            if ($sent) {
                $count++;
            }
        }

        $this->info("Successfully dispatched monthly report for {$targetDate->format('F Y')} to {$count} teachers.");
        return 0;
    }
}

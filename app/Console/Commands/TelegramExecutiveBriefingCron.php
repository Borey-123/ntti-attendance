<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;

class TelegramExecutiveBriefingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:executive-briefing {--shift=auto : Shift to brief for (auto, morning, afternoon, day)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily executive morning/afternoon attendance summary to Telegram management channel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $shift = (string)$this->option('shift');
        $this->info("Sending Daily Executive Briefing (Shift: {$shift})...");

        $result = TelegramService::sendDailyExecutiveBriefing($shift);

        if ($result['success']) {
            $this->info("Executive Briefing successfully dispatched to {$result['recipients']} recipient(s).");
            return Command::SUCCESS;
        }

        $this->error("Failed to dispatch Executive Briefing: " . ($result['message'] ?? 'Unknown error'));
        return Command::FAILURE;
    }
}

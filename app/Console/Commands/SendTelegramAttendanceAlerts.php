<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Teacher;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTelegramAttendanceAlerts extends Command
{
    protected $signature = 'telegram:attendance-alerts';
    protected $description = 'Send automated Telegram alerts for unexcused late or absent teachers';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $currentTime = Carbon::now()->format('H:i');

        $this->info("Scanning attendance records for today ($today) at $currentTime...");

        // Fetch teachers who have late entries today
        $lateAttendances = Attendance::with('teacher.department')
            ->where('date', $today)
            ->where(function ($q) {
                $q->where('morning_status', 'late')
                  ->orWhere('afternoon_status', 'late')
                  ->orWhere('evening_status', 'late');
            })->get();

        if ($lateAttendances->isEmpty()) {
            $this->info("No unexcused late records found for today.");
            return Command::SUCCESS;
        }

        $telegram = new TelegramService();
        $message = "⚠️ *NTTI AUTOMATED ATTENDANCE ALERT* ⚠️\n";
        $message .= "📅 Date: *" . Carbon::today()->format('Y-m-d') . "*\n";
        $message .= "⏰ Alert Time: *" . $currentTime . "*\n\n";
        $message .= "📋 *Late / Delayed Check-in List:*\n";

        foreach ($lateAttendances as $index => $att) {
            $teacherName = $att->teacher ? $att->teacher->name : 'Unknown';
            $dept = $att->teacher && $att->teacher->department ? $att->teacher->department->name : 'N/A';
            $message .= ($index + 1) . ". *" . $teacherName . "* (" . $dept . ")\n";
            if ($att->morning_status === 'late') $message .= "   └ Morning: " . ($att->morning_in ?? 'N/A') . "\n";
            if ($att->afternoon_status === 'late') $message .= "   └ Afternoon: " . ($att->afternoon_in ?? 'N/A') . "\n";
            if ($att->evening_status === 'late') $message .= "   └ Evening: " . ($att->evening_in ?? 'N/A') . "\n";
        }

        try {
            $telegram->broadcastMessage($message);
            $this->info("Successfully sent Telegram alert to Telegram channels.");
        } catch (\Exception $e) {
            $this->error("Failed to send Telegram alert: " . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}

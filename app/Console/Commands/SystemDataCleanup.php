<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\SecurityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class SystemDataCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive attendance data older than 1 year and delete security logs older than 3 months.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting System Data Cleanup...");

        $this->cleanupSecurityLogs();
        $this->archiveAttendances();

        $this->info("System Data Cleanup completed successfully.");
    }

    private function cleanupSecurityLogs()
    {
        $this->info("Cleaning up old security logs...");
        
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $deletedLogs = SecurityLog::where('timestamp', '<', $threeMonthsAgo)->delete();
        
        $this->info("Deleted {$deletedLogs} security logs older than 3 months.");
        SecurityLog::record("System Data Cleanup", "Deleted {$deletedLogs} security logs older than 3 months");
    }

    private function archiveAttendances()
    {
        $this->info("Archiving old attendances...");

        $oneYearAgo = Carbon::now()->subYear();
        
        // Fetch attendances to be archived
        $attendances = Attendance::with('teacher')->where('date', '<', $oneYearAgo)->get();

        if ($attendances->isEmpty()) {
            $this->info("No attendances older than 1 year found to archive.");
            return;
        }

        // Create CSV Content
        $csvContent = "Teacher Name,Employee ID,Date,Morning In,Morning Out,Morning Status,Afternoon In,Afternoon Out,Afternoon Status\n";
        
        foreach ($attendances as $att) {
            $name = $att->teacher->name ?? 'Unknown';
            $empId = $att->teacher->employee_id ?? 'Unknown';
            $csvContent .= "{$name},{$empId},{$att->date},{$att->morning_in},{$att->morning_out},{$att->morning_status},{$att->afternoon_in},{$att->afternoon_out},{$att->afternoon_status}\n";
        }

        // Ensure archive directory exists
        if (!Storage::disk('local')->exists('archives')) {
            Storage::disk('local')->makeDirectory('archives');
        }

        $filename = 'archives/attendance_archive_' . now()->format('Y-m-d_H-i-s') . '.csv';
        Storage::disk('local')->put($filename, $csvContent);

        $this->info("Archived {$attendances->count()} records to storage/app/{$filename}.");

        // Delete the archived records
        $deletedAttendances = Attendance::where('date', '<', $oneYearAgo)->delete();
        
        $this->info("Deleted {$deletedAttendances} attendance records older than 1 year.");
        SecurityLog::record("System Data Cleanup", "Archived and deleted {$deletedAttendances} attendance records older than 1 year");
    }
}

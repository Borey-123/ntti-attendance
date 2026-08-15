<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;

class AutoCheckoutAttendance extends Command
{
    protected $signature = 'attendance:auto-checkout';
    protected $description = 'Automatically check out teachers who forgot to scan at the end of their shift';

    public function handle()
    {
        $this->info('Starting auto-checkout process...');

        if (Setting::getValue('enable_auto_checkout', 'on') !== 'on') {
            $this->info('Auto-checkout is disabled in settings.');
            return;
        }
        
        $mEnd = Setting::getValue('morning_shift_end', '12:00');
        $aEnd = Setting::getValue('afternoon_shift_end', '18:30');
        $delay = (int)Setting::getValue('auto_checkout_delay', '30');
        
        $now = now();
        $today = $now->toDateString();
        $yesterday = $now->copy()->subDay()->toDateString();
        
        $records = Attendance::where(function($q) use ($today, $yesterday) {
                $q->whereDate('date', $today)->orWhereDate('date', $yesterday);
            })
            ->where(function($q) {
                $q->where(function($sq) {
                    $sq->whereNotNull('morning_in')->whereNull('morning_out');
                })->orWhere(function($sq) {
                    $sq->whereNotNull('afternoon_in')->whereNull('afternoon_out');
                });
            })
            ->get();

        $count = 0;
        foreach ($records as $record) {
            $updated = false;
            
            // Check Morning Shift
            if ($record->morning_in && !$record->morning_out) {
                $shiftEndTime = Carbon::parse($record->date->format('Y-m-d') . ' ' . $mEnd);
                if ($now->greaterThan($shiftEndTime->addMinutes($delay))) {
                    $record->morning_out = $mEnd;
                    $record->manual_note = trim(($record->manual_note ?? '') . ' [Auto Morning Checkout]');
                    $updated = true;
                }
            }

            // Check Afternoon Shift
            if ($record->afternoon_in && !$record->afternoon_out) {
                $shiftEndTime = Carbon::parse($record->date->format('Y-m-d') . ' ' . $aEnd);
                if ($now->greaterThan($shiftEndTime->addMinutes($delay))) {
                    $record->afternoon_out = $aEnd;
                    $record->manual_note = trim(($record->manual_note ?? '') . ' [Auto Afternoon Checkout]');
                    $updated = true;
                }
            }

            if ($updated) {
                $record->save();
                $count++;
            }
        }

        $this->info("Auto-checkout completed. Updated {$count} records.");
    }
}

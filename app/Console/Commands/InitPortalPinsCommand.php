<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class InitPortalPinsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:init-pins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize missing portal PINs for all existing teachers to 123456';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing Portal PINs for existing teachers...');

        $teachers = Teacher::whereNull('portal_pin')->orWhere('portal_pin', '')->get();

        if ($teachers->isEmpty()) {
            $this->info('All teachers already have a PIN assigned.');
            return;
        }

        $defaultPinHash = Hash::make('123456');
        $count = 0;

        $bar = $this->output->createProgressBar(count($teachers));

        foreach ($teachers as $teacher) {
            $teacher->update([
                'portal_pin' => $defaultPinHash
            ]);
            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully initialized PINs for {$count} teachers.");
    }
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== RFID CARDS ===\n";
foreach (App\Models\RfidCard::with('teacher')->get() as $c) {
    echo "ID: {$c->id} | UID: {$c->uid} | Status: {$c->status} | Teacher: " . ($c->teacher ? "[{$c->teacher->employee_id}] {$c->teacher->name} / {$c->teacher->name_kh}" : "None") . "\n";
}

echo "\n=== LATEST SECURITY LOGS ===\n";
foreach (Illuminate\Support\Facades\DB::table('security_logs')->orderBy('timestamp', 'desc')->take(10)->get() as $log) {
    echo "Action: {$log->action} | Target: {$log->target} | Details: {$log->details} | IP: {$log->ip_address} | Time: {$log->timestamp}\n";
}

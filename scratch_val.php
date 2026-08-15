<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/api-web/reports/attendance/manual', 'POST', [
    'teacher_id' => 1,
    'date' => '2026-08-14',
    'morning_in' => '07:30',
    'morning_out' => '11:30',
    'morning_status' => 'present',
    'afternoon_in' => '',
    'afternoon_out' => '',
    'afternoon_status' => 'none',
    'reason' => 'Forgot to scan',
]);

// We need to simulate the JSON request parsing
$request->headers->set('Accept', 'application/json');

$response = app('App\Http\Controllers\ReportController')->storeManualAttendance($request);
echo $response->getContent();

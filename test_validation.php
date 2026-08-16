<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$payload = [
    'teacher_id' => 1,
    'date' => '2026-08-16',
    'morning_in' => '08:00:00', // What if seconds are sent?
    'morning_out' => '12:00:00',
    'morning_status' => 'present',
    'afternoon_in' => '13:00:00',
    'afternoon_out' => '17:00:00',
    'afternoon_status' => 'present',
    'reason' => 'test'
];

$request = Illuminate\Http\Request::create('/api-web/reports/attendance/manual', 'POST', $payload);
$request->headers->set('Accept', 'application/json');

// Bypass CSRF by mocking the token
$app->instance(\Illuminate\Session\Store::class, new \Illuminate\Session\Store('test', new \SessionHandler()));
$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "BODY: " . $response->getContent() . "\n";

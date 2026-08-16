<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$payload = ['teacher_id' => 1];

// Disable CSRF middleware for this test
$app->bind(\App\Http\Middleware\VerifyCsrfToken::class, function () {
    return new class {
        public function handle($request, $next) { return $next($request); }
    };
});

// Create DB record if needed
$teacher = \App\Models\Teacher::firstOrCreate(['id' => 1], ['name' => 'Test Teacher', 'department' => 'Test']);

// Clear attendance for today to start fresh
\App\Models\Attendance::where('teacher_id', 1)->where('date', date('Y-m-d'))->delete();

// First Scan (Check-in)
$request1 = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', $payload);
$request1->headers->set('Accept', 'application/json');
$response1 = $kernel->handle($request1);
echo "SCAN 1 STATUS: " . $response1->getStatusCode() . "\n";
echo "SCAN 1 BODY: " . $response1->getContent() . "\n\n";

// Second Scan (Check-out)
$request2 = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', $payload);
$request2->headers->set('Accept', 'application/json');
$response2 = $kernel->handle($request2);
echo "SCAN 2 STATUS: " . $response2->getStatusCode() . "\n";
echo "SCAN 2 BODY: " . $response2->getContent() . "\n";

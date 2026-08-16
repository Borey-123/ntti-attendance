<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', ['teacher_id' => 1]);
$request->headers->set('Accept', 'application/json');

// Bypass CSRF token check
$app->bind(\App\Http\Middleware\VerifyCsrfToken::class, function () {
    return new class {
        public function handle($req, $next) { return $next($req); }
    };
});

// Setup dummy DB context if needed
$teacher = \App\Models\Teacher::find(1);
if (!$teacher) {
    echo "No teacher found for ID 1.\n";
    exit;
}
\App\Models\Attendance::where('teacher_id', 1)->where('date', date('Y-m-d'))->delete();

// SCAN 1
echo "SCAN 1:\n";
$res1 = $kernel->handle($request);
echo $res1->getContent() . "\n\n";

// Delay to simulate human interaction
sleep(2);

// SCAN 2
echo "SCAN 2:\n";
$request2 = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', ['teacher_id' => 1]);
$request2->headers->set('Accept', 'application/json');
$res2 = $kernel->handle($request2);
echo $res2->getContent() . "\n\n";

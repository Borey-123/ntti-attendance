<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$controller = $app->make(\App\Http\Controllers\AttendanceController::class);

$request1 = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', ['teacher_id' => 1]);
// We have to mock the validate method for the controller
try {
    $res1 = $controller->adminScan($request1);
} catch (\Exception $e) {
    echo "Scan 1 Error: " . $e->getMessage() . "\n";
}

// simulate a little bit of time passed
sleep(1);

$request2 = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', ['teacher_id' => 1]);
try {
    $res2 = $controller->adminScan($request2);
    echo "Scan 2 Body: " . $res2->getContent() . "\n";
} catch (\Exception $e) {
    echo "Scan 2 Error: " . $e->getMessage() . "\n";
}

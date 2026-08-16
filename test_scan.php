<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/api-web/attendance/admin-scan', 'POST', ['teacher_id' => 1]);
// We need to bypass middleware and call the controller directly
$controller = $app->make(\App\Http\Controllers\AttendanceController::class);
$response = $controller->adminScan($request);
echo $response->getContent();

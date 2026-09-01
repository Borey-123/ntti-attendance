<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $controller = new \App\Http\Controllers\AnalyticsController();
    $request = \Illuminate\Http\Request::create('/analytics', 'GET');
    $response = $controller->index($request);
    echo "ANALYTICS SUCCESS, status: " . $response->getStatusCode() . "\n";
} catch (\Throwable $e) {
    echo "ANALYTICS ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

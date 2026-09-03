<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $request = Illuminate\Http\Request::create('/reports/pdf', 'GET', ['month' => date('Y-m')]);
    $controller = new App\Http\Controllers\PdfReportController();
    $response = $controller->generate($request);
    echo "SUCCESS! Rendered HTML length: " . strlen($response->render());
} catch (\Throwable $e) {
    echo "EXCEPTION MESSAGE: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

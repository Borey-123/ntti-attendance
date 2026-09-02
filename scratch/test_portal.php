<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = \App\Models\Teacher::first();
if(!$t) {
    echo "No teacher found\n";
    exit;
}

echo "Testing Portal for Teacher ID: " . $t->employee_id . " (" . $t->name . ")\n";

session(['portal_teacher_id' => $t->employee_id]);

$controller = new \App\Http\Controllers\PortalController();
$req = \Illuminate\Http\Request::create('/portal', 'GET', ['employee_id' => $t->employee_id]);

try {
    $res = $controller->index($req);
    echo "Controller Executed Successfully! Rendered View Status: " . $res->getStatusCode() . "\n";
    $content = $res->render();
    echo "View Rendered Content Length: " . strlen($content) . " bytes\n";
} catch (\Throwable $e) {
    echo "ERROR OCCURRED:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

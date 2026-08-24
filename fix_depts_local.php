<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$json = file_get_contents(base_path('lang/km.json'));
$translations = json_decode($json, true);
$departments = \App\Models\Department::all();
$count = 0;
foreach ($departments as $dept) {
    if (isset($translations[$dept->name])) {
        $dept->name_kh = $translations[$dept->name];
        $dept->save();
        $count++;
        echo "Fixed: " . $dept->name . " -> " . $dept->name_kh . "\n";
    }
}
echo "Fixed $count departments.\n";

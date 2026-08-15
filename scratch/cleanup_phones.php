<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$duplicates = DB::table('teachers')
    ->select('phone')
    ->whereNotNull('phone')
    ->groupBy('phone')
    ->havingRaw('COUNT(*) > 1')
    ->pluck('phone');

foreach($duplicates as $p) {
    echo "Cleaning up duplicate phone: $p\n";
    DB::table('teachers')->where('phone', $p)->update(['phone' => null]);
}

echo "Cleanup complete.\n";

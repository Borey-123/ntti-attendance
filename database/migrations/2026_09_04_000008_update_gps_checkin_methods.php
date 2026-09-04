<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('attendance')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->update(['checkin_method' => 'gps']);
    }

    public function down(): void
    {
        // No-op rollback
    }
};

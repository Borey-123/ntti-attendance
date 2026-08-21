<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE teachers MODIFY photo LONGTEXT NULL;");
        } else {
            Schema::table('teachers', function (Blueprint $table) {
                $table->longText('photo')->change()->nullable();
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE teachers MODIFY photo VARCHAR(255) NULL;");
        } else {
            Schema::table('teachers', function (Blueprint $table) {
                $table->string('photo')->change()->nullable();
            });
        }
    }
};

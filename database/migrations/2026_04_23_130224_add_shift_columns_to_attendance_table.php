<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['check_in', 'check_out', 'status']);
            
            $table->time('morning_in')->nullable()->after('date');
            $table->time('morning_out')->nullable()->after('morning_in');
            $table->enum('morning_status', ['present', 'late', 'absent', 'none'])->default('none')->after('morning_out');
            
            $table->time('afternoon_in')->nullable()->after('morning_status');
            $table->time('afternoon_out')->nullable()->after('afternoon_in');
            $table->enum('afternoon_status', ['present', 'late', 'absent', 'none'])->default('none')->after('afternoon_out');
            
            $table->time('evening_in')->nullable()->after('afternoon_status');
            $table->time('evening_out')->nullable()->after('evening_in');
            $table->enum('evening_status', ['present', 'late', 'absent', 'none'])->default('none')->after('evening_out');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn([
                'morning_in', 'morning_out', 'morning_status',
                'afternoon_in', 'afternoon_out', 'afternoon_status',
                'evening_in', 'evening_out', 'evening_status'
            ]);
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'late', 'absent'])->default('present');
        });
    }
};

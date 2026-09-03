<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_schedules', function (Blueprint $table) {
            $table->string('subject_name')->nullable()->after('day_of_week');
            $table->string('room_number')->nullable()->after('subject_name');
            $table->foreignId('substitute_teacher_id')->nullable()->constrained('teachers')->onDelete('set null')->after('room_number');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_schedules', function (Blueprint $table) {
            $table->dropForeign(['substitute_teacher_id']);
            $table->dropColumn(['subject_name', 'room_number', 'substitute_teacher_id']);
        });
    }
};

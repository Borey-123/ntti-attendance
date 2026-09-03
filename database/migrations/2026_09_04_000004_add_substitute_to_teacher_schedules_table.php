<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_schedules', 'subject_name')) {
                $table->string('subject_name')->nullable()->after('day_of_week');
            }
            if (!Schema::hasColumn('teacher_schedules', 'room_number')) {
                $table->string('room_number')->nullable()->after('subject_name');
            }
            if (!Schema::hasColumn('teacher_schedules', 'substitute_teacher_id')) {
                $table->foreignId('substitute_teacher_id')->nullable()->constrained('teachers')->onDelete('set null')->after('room_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_schedules', 'substitute_teacher_id')) {
                $table->dropForeign(['substitute_teacher_id']);
                $table->dropColumn('substitute_teacher_id');
            }
            if (Schema::hasColumn('teacher_schedules', 'room_number')) {
                $table->dropColumn('room_number');
            }
            if (Schema::hasColumn('teacher_schedules', 'subject_name')) {
                $table->dropColumn('subject_name');
            }
        });
    }
};

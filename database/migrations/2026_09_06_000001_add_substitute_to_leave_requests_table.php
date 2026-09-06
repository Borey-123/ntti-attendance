<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_requests', 'substitute_teacher_id')) {
                $table->foreignId('substitute_teacher_id')->nullable()->after('teacher_id')->constrained('teachers')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'substitute_teacher_id')) {
                $table->dropForeign(['substitute_teacher_id']);
                $table->dropColumn('substitute_teacher_id');
            }
        });
    }
};

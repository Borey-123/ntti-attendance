<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->decimal('base_salary', 12, 2)->default(0)->after('status')
                  ->comment('Monthly base salary in USD');
            $table->string('position_rank')->nullable()->after('position')
                  ->comment('e.g. Senior Lecturer, Lecturer, Assistant');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'position_rank']);
        });
    }
};

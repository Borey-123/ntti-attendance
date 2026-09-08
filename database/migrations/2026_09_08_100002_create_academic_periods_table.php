<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('name');                              // e.g. "Semester 1"
            $table->string('name_kh')->nullable();               // e.g. "ឆមាសទី១"
            $table->enum('type', ['semester', 'term', 'exam', 'holiday_break', 'other'])->default('semester');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_attendance_required')->default(true);
            $table->string('color', 20)->default('#00d4a0');     // color for timeline view
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};

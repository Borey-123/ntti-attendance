<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->onDelete('set null');
            $table->date('month');                              // First day of month e.g. 2026-09-01

            // Attendance summary
            $table->integer('working_days')->default(0);        // Total workdays in period
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('late_count')->default(0);
            $table->integer('late_minutes_total')->default(0);
            $table->integer('approved_leave_days')->default(0);
            $table->integer('overtime_hours')->default(0);

            // Financial
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('daily_rate', 12, 2)->default(0);   // base_salary / working_days
            $table->decimal('gross_salary', 12, 2)->default(0); // present_days * daily_rate
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('absent_deduction', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);   // Final amount

            // Workflow
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // Each teacher can only have one payroll per month
            $table->unique(['teacher_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

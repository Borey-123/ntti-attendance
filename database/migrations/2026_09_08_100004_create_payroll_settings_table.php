<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->string('type')->default('decimal'); // decimal, integer, boolean, string
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default payroll settings
        DB::table('payroll_settings')->insert([
            ['key' => 'late_deduction_per_minute', 'value' => '0.50',  'type' => 'decimal', 'description' => 'USD deducted per minute late',                 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'absent_deduction_rate',     'value' => '1.0',   'type' => 'decimal', 'description' => 'Multiplier on daily rate per absent day',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency',                   'value' => 'USD',   'type' => 'string',  'description' => 'Currency code for payroll',                     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'currency_symbol',            'value' => '$',     'type' => 'string',  'description' => 'Currency symbol',                               'created_at' => now(), 'updated_at' => now()],
            ['key' => 'working_days_per_month',     'value' => '22',    'type' => 'integer', 'description' => 'Default working days per month',                'created_at' => now(), 'updated_at' => now()],
            ['key' => 'overtime_rate_per_hour',     'value' => '2.50',  'type' => 'decimal', 'description' => 'USD per overtime hour',                         'created_at' => now(), 'updated_at' => now()],
            ['key' => 'approved_leave_is_paid',     'value' => '1',     'type' => 'boolean', 'description' => 'Whether approved leave days are paid',          'created_at' => now(), 'updated_at' => now()],
            ['key' => 'payroll_note_footer',        'value' => 'ប្រាក់ខែនេះបានគណនាដោយស្វ័យប្រវត្តិ ផ្អែកលើទិន្នន័យវត្តមានពី NTTI Attendance System', 'type' => 'string', 'description' => 'Footer note on payslip', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};

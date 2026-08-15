<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->enum('status', ['present', 'late', 'absent'])->default('present');
            $table->string('rfid_uid')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'date']);
            $table->index('date');
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};

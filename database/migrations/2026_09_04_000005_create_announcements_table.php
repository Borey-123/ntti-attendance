<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_kh')->nullable();
            $table->text('content');
            $table->text('content_kh')->nullable();
            $table->string('priority')->default('info'); // info, warning, urgent
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->boolean('send_telegram')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

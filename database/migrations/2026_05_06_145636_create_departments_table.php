<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->foreignId('head_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();
        });

        // Migrate existing departments from teachers table
        $existingDepartments = DB::table('teachers')->select('department')->distinct()->pluck('department');
        foreach ($existingDepartments as $dept) {
            if ($dept) {
                DB::table('departments')->insert([
                    'name' => $dept,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};

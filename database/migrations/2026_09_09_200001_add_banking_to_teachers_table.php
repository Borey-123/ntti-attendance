<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('base_salary')
                  ->comment('e.g. ABA Bank, ACLEDA Bank, Canadia Bank, Bakong');
            $table->string('bank_account_number')->nullable()->after('bank_name')
                  ->comment('Account number e.g. 001 234 567');
            $table->string('bank_account_name')->nullable()->after('bank_account_number')
                  ->comment('Account holder name in English e.g. CHAN BOREY');
            $table->string('bakong_account_id')->nullable()->after('bank_account_name')
                  ->comment('Bakong ID e.g. chan_borey@aclb or 012345678@abaa');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'bakong_account_id',
            ]);
        });
    }
};

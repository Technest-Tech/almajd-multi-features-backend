<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['admin', 'teacher', 'student'])->default('student')->after('email');
            $table->string('whatsapp_number')->nullable()->after('user_type');
            $table->string('country', 2)->nullable()->after('whatsapp_number'); // ISO country code
            $table->enum('currency', ['USD', 'GBP', 'EUR', 'EGP', 'SAR', 'AED', 'CAD'])->nullable()->after('country');
            $table->decimal('hour_price', 10, 2)->nullable()->after('currency');
            $table->string('bank_name')->nullable()->after('hour_price');
            $table->string('account_number')->nullable()->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_type',
                'whatsapp_number',
                'country',
                'currency',
                'hour_price',
                'bank_name',
                'account_number',
            ]);
        });
    }
};

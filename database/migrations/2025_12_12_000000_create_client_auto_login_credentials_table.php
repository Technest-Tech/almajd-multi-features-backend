<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_auto_login_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password'); // Will be encrypted
            $table->timestamps();
        });

        // Insert default credentials
        DB::table('client_auto_login_credentials')->insert([
            'email' => 'almajd@admin.com',
            'password' => encrypt('almajd123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_auto_login_credentials');
    }
};

<?php

use App\Enums\UserType;
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
        // Set all teachers' currency to EGP
        DB::table('users')
            ->where('user_type', UserType::Teacher->value)
            ->update(['currency' => 'EGP']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we don't know the original currency values
        // This is intentional - teachers should always use EGP
    }
};

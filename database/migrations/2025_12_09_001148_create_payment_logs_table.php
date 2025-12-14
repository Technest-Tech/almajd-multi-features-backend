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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('pid')->nullable()->comment('Payment ID from AnubPay');
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->string('billing_type')->nullable()->comment('auto or manual');
            $table->unsignedBigInteger('family_id')->nullable()->comment('Legacy field for compatibility');
            $table->string('month')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamps();
            
            $table->index(['billing_id', 'billing_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};

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
        Schema::create('manual_billings', function (Blueprint $table) {
            $table->id();
            $table->json('student_ids')->comment('Array of student IDs');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->text('message')->nullable();
            $table->string('payment_token')->unique()->nullable()->comment('Unique token for payment link');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['is_paid']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_billings');
    }
};

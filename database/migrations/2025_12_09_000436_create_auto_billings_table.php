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
        Schema::create('auto_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->decimal('total_hours', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_token')->unique()->nullable()->comment('Unique token for payment link');
            $table->timestamps();
            
            $table->index(['student_id']);
            $table->index(['year', 'month']);
            $table->index(['is_paid']);
            $table->unique(['student_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_billings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->string('xpay_session_id')->nullable()->after('transaction_id');
            $table->string('xpay_payment_intent_id')->nullable()->after('xpay_session_id');
        });

        Schema::create('processed_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropColumn(['xpay_session_id', 'xpay_payment_intent_id']);
        });

        Schema::dropIfExists('processed_webhook_events');
    }
};

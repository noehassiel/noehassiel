<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('stripe_balance_checkout_session_id')->nullable()->index()->after('balance_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropIndex(['stripe_balance_checkout_session_id']);
            $table->dropColumn('stripe_balance_checkout_session_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The second 50%, paid when the complete project is delivered.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('balance_payment_method')->nullable()->after('deposit_paid_at');
            $table->decimal('balance_paid_amount', 12, 2)->nullable()->after('balance_payment_method');
            $table->timestamp('balance_paid_at')->nullable()->after('balance_paid_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['balance_payment_method', 'balance_paid_amount', 'balance_paid_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The client's answer on the public page, the deposit payment, and the project it turns into.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->boolean('requires_invoice')->default(false)->after('tax_rate');
            $table->text('client_response')->nullable()->after('client_signed_at'); // requested changes or reason to cancel
            $table->timestamp('responded_at')->nullable()->after('client_response');
            $table->string('payment_method')->nullable()->after('responded_at');
            $table->string('stripe_checkout_session_id')->nullable()->index()->after('payment_method');
            $table->decimal('deposit_paid_amount', 12, 2)->nullable()->after('stripe_checkout_session_id');
            $table->timestamp('deposit_paid_at')->nullable()->after('deposit_paid_amount');
            $table->foreignId('project_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropIndex(['stripe_checkout_session_id']);
            $table->dropColumn([
                'requires_invoice',
                'client_response',
                'responded_at',
                'payment_method',
                'stripe_checkout_session_id',
                'deposit_paid_amount',
                'deposit_paid_at',
            ]);
        });
    }
};

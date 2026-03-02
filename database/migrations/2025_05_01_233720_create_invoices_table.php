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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade');
            $table->string('number')->nullable();
            $table->string('status')->default('draft'); // draft, sent, paid
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('currency')->default('MXN'); // Default currency
            $table->string('payment_method')->nullable(); // e.g., bank transfer, credit card
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

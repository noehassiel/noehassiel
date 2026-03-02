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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('number')->unique(); // e.g. PROP-2025-001
            $table->string('title');
            $table->string('project_type'); // website, webapp, branding, ecommerce, mobile, custom
            $table->text('what_we_do'); // description of what will be done
            $table->text('how_we_do_it'); // methodology / approach
            $table->json('includes')->nullable(); // array of strings: what's included
            $table->json('excludes')->nullable(); // array of strings: what's NOT included
            $table->text('notes')->nullable(); // additional notes / terms
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
            $table->string('currency')->default('MXN');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // percentage, e.g. 16 for 16%
            $table->decimal('total', 12, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('client_signature')->nullable();
            $table->timestamp('client_signed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};

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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('status')->default('new')->index()->after('language');
            $table->text('notes')->nullable()->after('status');
            // Set once the deal closes and the lead becomes (or joins) a client
            $table->foreignId('client_id')->nullable()->after('notes')->constrained()->nullOnDelete();
            $table->timestamp('converted_at')->nullable()->after('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn(['status', 'notes', 'converted_at']);
        });
    }
};

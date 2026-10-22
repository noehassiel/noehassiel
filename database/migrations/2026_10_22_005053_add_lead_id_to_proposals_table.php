<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A proposal can now be addressed to a lead (prospect) instead of a client.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
            $table->foreignId('lead_id')->nullable()->after('client_id')->constrained('leads')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};

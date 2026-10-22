<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who shared the folder: null for the admin, the client user otherwise, so clients only remove their own.
     */
    public function up(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->foreignId('added_by')->nullable()->after('url')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('added_by');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A shareable, password-protected public page for each proposal, plus view tracking.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('pdf_path');
            $table->text('access_password')->nullable()->after('public_token'); // encrypted, so the admin can copy it again
            $table->timestamp('first_viewed_at')->nullable()->after('sent_at');
            $table->timestamp('last_viewed_at')->nullable()->after('first_viewed_at');
            $table->unsignedInteger('view_count')->default(0)->after('last_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn(['public_token', 'access_password', 'first_viewed_at', 'last_viewed_at', 'view_count']);
        });
    }
};

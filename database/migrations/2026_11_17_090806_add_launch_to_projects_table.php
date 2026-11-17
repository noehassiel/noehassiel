<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the finished site waits in maintenance mode, and how to bring it up once the project is paid.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('launch_url')->nullable();
            $table->text('launch_token')->nullable();
            $table->timestamp('launched_at')->nullable();
            $table->string('launch_error')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['launch_url', 'launch_token', 'launched_at', 'launch_error']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where the mirrored Linear project lives; linear_project_id already exists from the original table.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('linear_project_url')->nullable()->after('linear_project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('linear_project_url');
        });
    }
};

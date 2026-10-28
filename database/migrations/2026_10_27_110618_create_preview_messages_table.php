<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The review chat on a project's preview, shared by the client's team and kept across visits.
     */
    public function up(): void
    {
        Schema::create('preview_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role'); // user | assistant
            $table->text('content');
            $table->string('page_url', 2048)->nullable();
            $table->json('pending_quote')->nullable(); // an out-of-scope request waiting for the client's go-ahead
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preview_messages');
    }
};

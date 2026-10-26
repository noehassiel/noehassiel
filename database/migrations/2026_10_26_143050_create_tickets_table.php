<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Requests a client opens from the portal on an active project; each becomes a Linear issue.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // see App\Enums\TicketType
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('open'); // see App\Enums\TicketStatus
            $table->string('linear_issue_id')->nullable()->unique();
            $table->string('linear_identifier')->nullable();
            $table->string('linear_url')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

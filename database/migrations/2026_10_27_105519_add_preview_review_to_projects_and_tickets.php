<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The client reviews a live preview of their project, asks for changes in rounds and approves it.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('preview_url')->nullable()->after('linear_project_url');
            $table->text('self_service_notes')->nullable()->after('preview_url'); // what the client can change from their own admin panel
            $table->unsignedTinyInteger('revision_rounds')->default(2)->after('self_service_notes');
            $table->unsignedTinyInteger('current_round')->default(1)->after('revision_rounds');
            $table->timestamp('approved_at')->nullable()->after('current_round');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('page_url', 2048)->nullable()->after('description');
            $table->string('category')->nullable()->after('page_url'); // see App\Enums\ChangeCategory
            $table->boolean('in_scope')->nullable()->after('category');
            $table->unsignedTinyInteger('round')->nullable()->after('in_scope');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['page_url', 'category', 'in_scope', 'round']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['preview_url', 'self_service_notes', 'revision_rounds', 'current_round', 'approved_at']);
        });
    }
};

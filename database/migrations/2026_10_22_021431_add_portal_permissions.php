<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Client teams: owners manage the team and see everything; members see what the owner allows.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('portal_role')->nullable()->after('client_id'); // see App\Enums\PortalRole
            $table->boolean('can_view_all_projects')->default(true)->after('portal_role');
            $table->boolean('can_view_invoices')->default(true)->after('can_view_all_projects');
        });

        // Everyone already in a portal was invited by the admin, so they own their team
        DB::table('users')->whereNotNull('client_id')->update(['portal_role' => 'owner']);

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::table('client_invitations', function (Blueprint $table) {
            $table->string('portal_role')->default('owner')->after('locale');
            $table->boolean('can_view_all_projects')->default(true)->after('portal_role');
            $table->boolean('can_view_invoices')->default(true)->after('can_view_all_projects');
            $table->json('project_ids')->nullable()->after('can_view_invoices');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_invitations', function (Blueprint $table) {
            $table->dropColumn(['portal_role', 'can_view_all_projects', 'can_view_invoices', 'project_ids']);
        });

        Schema::dropIfExists('project_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['portal_role', 'can_view_all_projects', 'can_view_invoices']);
        });
    }
};

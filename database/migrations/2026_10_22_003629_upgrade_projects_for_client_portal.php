<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->text('progress_note')->nullable()->after('status');
            $table->date('starts_on')->nullable()->after('progress_note');
            $table->date('due_on')->nullable()->after('starts_on');
        });

        // Map the old free-form statuses onto ProjectStatus
        DB::table('projects')->where('status', 'active')->update(['status' => 'development']);
        DB::table('projects')->where('status', 'finished')->update(['status' => 'delivered']);

        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('brief')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('projects')->where('status', 'delivered')->update(['status' => 'finished']);
        DB::table('projects')->whereNotIn('status', ['finished', 'paused'])->update(['status' => 'active']);

        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
            $table->dropColumn(['description', 'progress_note', 'starts_on', 'due_on']);
        });
    }
};

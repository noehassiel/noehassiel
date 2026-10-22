<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The back-and-forth on a proposal that the client sees: their requests and each update.
     */
    public function up(): void
    {
        Schema::create('proposal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // see App\Enums\ProposalEventType
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['proposal_id', 'created_at']);
        });

        // Keep answers given before the history existed
        DB::table('proposals')
            ->whereNotNull('responded_at')
            ->whereIn('status', ['changes_requested', 'accepted', 'rejected', 'paid'])
            ->get(['id', 'status', 'client_response', 'responded_at'])
            ->each(fn (object $proposal) => DB::table('proposal_events')->insert([
                'proposal_id' => $proposal->id,
                'type' => $proposal->status === 'paid' ? 'accepted' : $proposal->status,
                'body' => $proposal->client_response,
                'created_at' => $proposal->responded_at,
                'updated_at' => $proposal->responded_at,
            ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_events');
    }
};

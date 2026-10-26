<?php

use App\Enums\PaymentMethod;
use App\Enums\TicketStatus;
use App\Jobs\CreateLinearIssueForTicket;
use App\Jobs\SyncProjectToLinear;
use App\Livewire\Portal\ProjectShow;
use App\Mail\TicketOpenedMail;
use App\Models\Client;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\ProposalStage;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Linear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.linear.api_key' => 'lin_api_test',
        'services.linear.team_id' => 'team-123',
        'services.linear.webhook_secret' => 'whsec_linear',
    ]);

    Mail::fake();
});

function fakeLinear(): void
{
    Http::fake(['api.linear.app/*' => function (Request $request) {
        return str_contains($request['query'], 'projectCreate')
            ? Http::response(['data' => ['projectCreate' => ['success' => true, 'project' => ['id' => 'proj-1', 'url' => 'https://linear.app/x/project/proj-1']]]])
            : Http::response(['data' => ['issueCreate' => ['success' => true, 'issue' => ['id' => 'issue-'.uniqid(), 'identifier' => 'NH-7', 'url' => 'https://linear.app/x/issue/NH-7']]]]);
    }]);
}

// ─── Projects ─────────────────────────────────────────────────────────────────

test('a paid deposit mirrors the project in Linear with one issue per stage', function () {
    fakeLinear();
    $proposal = Proposal::factory()->accepted()->create(['client_id' => Client::factory()]);
    ProposalStage::factory()->count(2)->create(['proposal_id' => $proposal->id]);

    Bus::fake([SyncProjectToLinear::class]);
    $project = $proposal->markDepositPaid(PaymentMethod::Transfer, 1000);
    Bus::assertDispatchedAfterResponse(SyncProjectToLinear::class);

    // Bus is faked above, so run the job by hand
    (new SyncProjectToLinear($project))->handle(app(Linear::class));

    expect($project->fresh())
        ->linear_project_id->toBe('proj-1')
        ->linear_project_url->toBe('https://linear.app/x/project/proj-1');

    Http::assertSentCount(3);
    Http::assertSent(fn (Request $request) => $request->header('Authorization')[0] === 'lin_api_test'
        && in_array('team-123', $request['variables']['input']['teamIds'] ?? [$request['variables']['input']['teamId'] ?? null], true));
});

test('syncing twice never creates a second Linear project', function () {
    fakeLinear();
    $project = Project::factory()->create(['linear_project_id' => 'proj-1']);

    SyncProjectToLinear::dispatchSync($project);

    Http::assertNothingSent();
});

test('nothing is sent when Linear is not configured', function () {
    config(['services.linear.api_key' => null]);
    Http::fake();

    SyncProjectToLinear::dispatchSync(Project::factory()->create());

    Http::assertNothingSent();
});

// ─── Tickets ──────────────────────────────────────────────────────────────────

test('a client opens a request from the portal and the admin is told', function () {
    $client = Client::factory()->create();
    $project = Project::factory()->for($client)->create();
    $user = User::factory()->forClient($client)->create(['locale' => 'es']);
    $this->actingAs($user);
    Bus::fake([CreateLinearIssueForTicket::class]);

    Livewire::test(ProjectShow::class, ['project' => $project])
        ->set('ticketType', 'bug')
        ->set('ticketTitle', 'El formulario no envía')
        ->set('ticketDescription', 'En el celular, al darle enviar no pasa nada.')
        ->call('submitTicket')
        ->assertHasNoErrors()
        ->assertSee('El formulario no envía');

    expect(Ticket::sole())->user_id->toBe($user->id)->status->toBe(TicketStatus::Open);
    Mail::assertSent(TicketOpenedMail::class);
    Bus::assertDispatchedAfterResponse(CreateLinearIssueForTicket::class);
});

test('a ticket becomes an issue in its project, mirroring the project first if needed', function () {
    fakeLinear();
    $ticket = Ticket::factory()->create();

    CreateLinearIssueForTicket::dispatchSync($ticket);

    expect($ticket->fresh())->linear_identifier->toBe('NH-7')
        ->and($ticket->project->fresh()->linear_project_id)->toBe('proj-1');
});

// ─── Webhook ──────────────────────────────────────────────────────────────────

function linearWebhook(array $payload, ?string $secret = 'whsec_linear')
{
    $body = json_encode($payload);

    return test()->call('POST', route('linear.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_LINEAR_SIGNATURE' => hash_hmac('sha256', $body, $secret),
    ], $body);
}

test('closing the issue in Linear resolves the ticket for the client', function () {
    $ticket = Ticket::factory()->create(['linear_issue_id' => 'issue-abc']);

    linearWebhook([
        'type' => 'Issue',
        'action' => 'update',
        'data' => ['id' => 'issue-abc', 'state' => ['type' => 'completed']],
        'webhookTimestamp' => now()->getTimestampMs(),
    ])->assertNoContent();

    expect($ticket->fresh())->status->toBe(TicketStatus::Resolved)->resolved_at->not->toBeNull();
});

test('webhooks with a bad signature or that are too old are rejected', function () {
    $ticket = Ticket::factory()->create(['linear_issue_id' => 'issue-abc']);
    $payload = ['type' => 'Issue', 'data' => ['id' => 'issue-abc', 'state' => ['type' => 'completed']]];

    linearWebhook([...$payload, 'webhookTimestamp' => now()->getTimestampMs()], 'wrong-secret')->assertUnauthorized();
    linearWebhook([...$payload, 'webhookTimestamp' => now()->subMinutes(5)->getTimestampMs()])->assertUnauthorized();

    expect($ticket->fresh()->status)->toBe(TicketStatus::Open);
});

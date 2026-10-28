<?php

namespace App\Livewire\Portal;

use App\Ai\Agents\ChangeRequestTriage;
use App\Enums\ChangeCategory;
use App\Enums\TicketType;
use App\Jobs\CreateLinearIssueForTicket;
use App\Mail\ProjectApprovedMail;
use App\Mail\TicketOpenedMail;
use App\Models\PreviewMessage;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

/**
 * The client reviews the live preview of their project full-screen and asks for changes in a side chat.
 * The AI checks each request against the accepted proposal; actionable ones go to Linear.
 */
#[Layout('components.layouts.preview')]
class ProjectPreview extends Component
{
    public Project $project;

    public string $message = '';

    /** The path the client is looking at, reported by the preview bridge inside the iframe. */
    public string $pagePath = '/';

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);
        abort_unless($project->preview_url, 404);

        $this->project = $project;
    }

    public function send(): void
    {
        $this->authorize('requestChanges', $this->project);

        $validated = $this->validate(
            ['message' => ['required', 'string', 'max:5000']],
            attributes: ['message' => __('portal.preview.message')],
        );

        $this->resolvePendingQuote();
        $history = $this->project->previewMessages()->reorder()->latest('id')->limit(40)->get()->reverse()
            ->map(fn (PreviewMessage $entry): array => ['role' => $entry->role, 'content' => $entry->forAgent()])
            ->values()->all();

        $question = $this->project->previewMessages()->make([
            'user_id' => Auth::id(),
            'role' => 'user',
            'content' => $validated['message'],
            'page_url' => $this->pageUrl(),
        ]);

        try {
            $triage = (new ChangeRequestTriage($this->project->load('proposal.stages', 'proposal.items'), $history))->prompt($question->forAgent());
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('message', __('portal.preview.ai_failed'));

            return;
        }

        $question->save();
        $this->reset('message');

        $category = ChangeCategory::tryFrom($triage['category']);
        $pendingQuote = $triage['action'] === 'out_of_scope' && $category
            ? ['category' => $category->value, 'title' => $triage['title'], 'description' => $triage['description']]
            : null;

        $this->project->previewMessages()->create(['role' => 'assistant', 'content' => $triage['reply'], 'pending_quote' => $pendingQuote]);

        if ($triage['action'] === 'create' && $category) {
            $this->openTicket($category, $triage['title'], $triage['description'], inScope: true);
        }
    }

    /** The client wants the out-of-scope change anyway: it goes to Linear to be quoted. */
    public function confirmQuote(): void
    {
        $this->authorize('requestChanges', $this->project);
        $quote = $this->pendingQuote();
        abort_unless($quote, 403);

        $this->openTicket(
            ChangeCategory::from($quote->pending_quote['category']),
            '[Por cotizar] '.$quote->pending_quote['title'],
            $quote->pending_quote['description'],
            inScope: false,
        );

        $this->resolvePendingQuote(__('portal.preview.quote_requested'));
    }

    public function declineQuote(): void
    {
        $this->authorize('requestChanges', $this->project);
        abort_unless($this->pendingQuote(), 403);

        $this->resolvePendingQuote(__('portal.preview.quote_declined'));
    }

    /** The last reply, when it's still asking the client whether to quote something. */
    private function pendingQuote(): ?PreviewMessage
    {
        $last = $this->project->previewMessages()->reorder()->latest('id')->first();

        return $last?->pending_quote ? $last : null;
    }

    /** Close an open quote question, with the outcome as a reply; a new message also closes it. */
    private function resolvePendingQuote(?string $reply = null): void
    {
        $this->pendingQuote()?->update(['pending_quote' => null]);

        if ($reply) {
            $this->project->previewMessages()->create(['role' => 'assistant', 'content' => $reply]);
        }
    }

    public function approve(): void
    {
        $this->authorize('approve', $this->project);

        $this->project->approve(Auth::user());
        Mail::to(config('app.admin_email'))->send(new ProjectApprovedMail($this->project->fresh(['client', 'approver'])));

        $this->redirectRoute('portal.projects.show', $this->project);
    }

    private function openTicket(ChangeCategory $category, string $title, string $description, bool $inScope): void
    {
        $ticket = $this->project->tickets()->create([
            'user_id' => Auth::id(),
            'type' => $category === ChangeCategory::Bug ? TicketType::Bug : TicketType::Change,
            'title' => $title,
            'description' => $description,
            'page_url' => $this->pageUrl(),
            'category' => $category,
            'in_scope' => $inScope,
            'round' => $inScope && $category->countsTowardRounds() ? $this->project->current_round : null,
        ]);

        Mail::to(config('app.admin_email'))->send(new TicketOpenedMail($ticket));
        CreateLinearIssueForTicket::dispatchAfterResponse($ticket);
    }

    /** The full preview URL for the path the client is on; only paths are accepted from the browser. */
    private function pageUrl(): string
    {
        $path = '/'.ltrim(parse_url($this->pagePath, PHP_URL_PATH) ?: '/', '/');

        return rtrim($this->project->preview_url, '/').$path;
    }

    public function render(): View
    {
        return view('livewire.portal.project-preview', [
            'messages' => $this->project->previewMessages()->with('user')->get(),
            'pendingQuote' => $this->pendingQuote(),
            'tickets' => $this->project->tickets()->whereNotNull('category')->latest()->get(),
            'canRequestChanges' => Auth::user()->can('requestChanges', $this->project),
            'canApprove' => Auth::user()->can('approve', $this->project),
            'backUrl' => Auth::user()->can('access-admin') ? route('projects.show', $this->project) : route('portal.projects.show', $this->project),
            'previewOrigin' => parse_url($this->project->preview_url, PHP_URL_SCHEME).'://'.parse_url($this->project->preview_url, PHP_URL_HOST).(parse_url($this->project->preview_url, PHP_URL_PORT) ? ':'.parse_url($this->project->preview_url, PHP_URL_PORT) : ''),
        ])->title(__('portal.preview.title', ['project' => $this->project->name]));
    }
}

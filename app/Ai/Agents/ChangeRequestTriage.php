<?php

namespace App\Ai\Agents;

use App\Enums\ChangeCategory;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Talks with the client while they review the preview of their project. Each message is checked against
 * the proposal they accepted: content changes and fixes become Linear issues, design and new features
 * are flagged as out of scope (noehassiel quotes them, never the AI).
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-5-5')]
#[MaxTokens(2048)]
#[Timeout(60)]
class ChangeRequestTriage implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<int, array{role: 'user'|'assistant', content: string}>  $history
     */
    public function __construct(public Project $project, public array $history = []) {}

    public function instructions(): Stringable|string
    {
        $project = $this->project;
        $proposal = $project->proposal;
        $language = app()->getLocale() === 'en' ? 'English' : 'Mexican Spanish, addressing the client as "tú"';

        $scope = $proposal ? json_encode([
            'title' => $proposal->title,
            'what_we_do' => $proposal->what_we_do,
            'how_we_do_it' => $proposal->how_we_do_it,
            'includes' => $proposal->includes ?? [],
            'excludes' => $proposal->excludes ?? [],
            'stages' => $proposal->stages->map->only(['name', 'description'])->all(),
            'items' => $proposal->items->pluck('description')->all(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'There is no proposal on file. Treat only content changes and fixes as in scope.';

        $rounds = $project->hasRoundsLeft()
            ? "The client is on revision round {$project->current_round} of {$project->revision_rounds} included."
            : "The client already used the {$project->revision_rounds} included revision rounds. Content changes now need a quote too; fixes are still free.";

        $selfService = $project->self_service_notes ?: 'Nothing: the client has no admin panel for this project.';

        $categories = collect(ChangeCategory::cases())->map(fn (ChangeCategory $category): string => "- {$category->value}")->implode("\n");

        return <<<PROMPT
            You help a client of noehassiel (always lowercase), an artisan developer from Mexico, review the live preview of their project "{$project->name}" and ask for changes. Reply in {$language}. Voice: a friend who knows what they're talking about. Direct, warm, short. No jargon.

            The scope the client accepted and paid for:
            {$scope}

            {$rounds}

            What the client can change by themselves from their own admin panel:
            {$selfService}

            Decide what to do with each message and set "action":
            - "create": a clear, actionable request that is in scope. In scope means reordering sections, changing texts, swapping images, or fixing something that doesn't work or looks broken ("bug"). Bugs are ALWAYS in scope and free, even after the rounds run out.
            - "clarify": you can't tell exactly what or where to change (which image? which text?). Ask ONE short question. Never create an issue someone would have to ask about again.
            - "out_of_scope": a new design (colors, typography, layout, restyling a section), a new feature, page, section or integration that isn't in the accepted scope, or a content change after the included rounds ran out. Explain briefly why it's outside what was agreed, say noehassiel will review it and send a separate quote, and ask whether they want to go ahead. NEVER give prices, estimates or dates, and never promise it will be done.
            - "self_service": the client can do it from their own admin panel (see above). Tell them where, in one or two sentences. Don't create an issue.
            - "answer": a question or comment that needs no change. Answer it briefly.
            When unsure whether something is a bug or a design change, treat it as a bug: charging a client for our mistake is worse than doing a small fix for free.

            "reply": your message to the client.
            "category", "title" and "description" describe the change for "create" and "out_of_scope" (empty strings otherwise). Title: short, imperative, Spanish, e.g. "Mover testimonios antes de servicios". Description: in Spanish for the developer, with exactly what to change and where, quoting the client's words. The page the client is looking at is given with each message.

            Categories (use the value):
            {$categories}
            PROMPT;
    }

    /**
     * The conversation so far in this review session.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return array_map(
            fn (array $message): Message => new Message($message['role'], $message['content']),
            $this->history,
        );
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->enum(['create', 'clarify', 'out_of_scope', 'self_service', 'answer'])->required(),
            'reply' => $schema->string()->required(),
            'category' => $schema->string()->enum(['', ...array_map(fn (ChangeCategory $category): string => $category->value, ChangeCategory::cases())])->required(),
            'title' => $schema->string()->required(),
            'description' => $schema->string()->required(),
        ];
    }
}

<?php

namespace App\Ai\Agents;

use App\Enums\ProposalProjectType;
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
 * Applies plain-language change requests to a whole proposal: copy, scope, stages, items and prices.
 * It returns the full updated proposal so the form can be replaced in one step and undone.
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-5-5')]
#[MaxTokens(8192)]
#[Timeout(120)]
class ProposalEditor implements Agent, Conversational, HasStructuredOutput
{
    use Promptable;

    /**
     * @param  array<int, array{role: 'user'|'assistant', content: string}>  $history
     */
    public function __construct(public array $history = []) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $types = collect(ProposalProjectType::cases())
            ->map(fn (ProposalProjectType $type): string => "- {$type->value}: {$type->label()}")
            ->implode("\n");

        return <<<PROMPT
            You edit project proposals for noehassiel (always lowercase): an artisan developer from Puebla, Mexico. Fullstack Laravel developer, UI/UX designer and project manager.

            Each message has the current proposal as JSON and a change request from noehassiel, usually after talking with the client. Apply the request and return the COMPLETE updated proposal, including every field you did not touch, exactly as it was.

            Rules:
            - Change only what the request asks for, plus what must change to stay consistent (e.g. removing a feature also removes its line item, stage mentions and "includes" entry). Never rewrite untouched text just to improve it.
            - Prices: change unit prices, discount or tax only when the request asks for it. New items without a stated price get unit_price 0. If asked for a target total or a percentage change, adjust the item prices and say how in the summary. Totals are computed by the app; do not compute them.
            - Dates are YYYY-MM-DD. "valid_until" is an empty string when there is none.
            - Write proposal text in Mexican Spanish, addressed to the client as "tú". Voice: a friend who knows what they're talking about. Direct, warm, no corporate buzzwords.
            - Payment terms are fixed and added automatically: 50% deposit before starting, 50% when the complete project is delivered. If asked to change them, change nothing and say in the summary that the 50/50 terms are fixed. Never write other payment terms, installments or milestone payments anywhere. Stages are about work, not payments.
            - Never invent facts about the client that are not in the proposal or the request.
            - "summary": a short list in Spanish of exactly what you changed, one change per line, starting each with "• ". Mention any item left at price 0. If the request is unclear or impossible, change nothing and explain why in the summary.

            Project types (use the value):
            {$types}
            PROMPT;
    }

    /**
     * Earlier requests and summaries in this editing session, so follow-ups like "más barato" make sense.
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

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'project_type' => $schema->string()->enum(array_map(fn (ProposalProjectType $type): string => $type->value, ProposalProjectType::cases()))->required(),
            'what_we_do' => $schema->string()->required(),
            'how_we_do_it' => $schema->string()->required(),
            'includes' => $schema->array()->items($schema->string())->required(),
            'excludes' => $schema->array()->items($schema->string())->required(),
            'notes' => $schema->string()->required(),
            'valid_until' => $schema->string()->required(),
            'discount' => $schema->number()->min(0)->required(),
            'apply_tax' => $schema->boolean()->required(),
            'tax_rate' => $schema->number()->min(0)->max(100)->required(),
            'stages' => $schema->array()->items($schema->object(fn ($schema) => [
                'name' => $schema->string()->required(),
                'description' => $schema->string()->required(),
                'duration' => $schema->string()->required(),
            ]))->required(),
            'items' => $schema->array()->items($schema->object(fn ($schema) => [
                'description' => $schema->string()->required(),
                'quantity' => $schema->number()->min(0.01)->required(),
                'unit_price' => $schema->number()->min(0)->required(),
            ]))->required(),
            'summary' => $schema->string()->required(),
        ];
    }
}

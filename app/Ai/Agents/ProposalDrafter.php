<?php

namespace App\Ai\Agents;

use App\Enums\ProposalProjectType;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Stringable;

/**
 * Turns rough notes about a project into a first draft of a proposal.
 * It writes scope and copy only; prices are always left for a human to set.
 */
#[Provider(Lab::Anthropic)]
#[Model('claude-sonnet-5-5')]
#[MaxTokens(4096)]
#[Timeout(90)]
class ProposalDrafter implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $services = collect(ProposalProjectType::cases())
            ->map(fn (ProposalProjectType $service): string => "- {$service->label()}: {$service->scope()}")
            ->implode("\n");

        return <<<PROMPT
            You draft project proposals for noehassiel (always lowercase): an artisan developer from Puebla, Mexico. Fullstack Laravel developer, UI/UX designer and project manager who builds premium, hand-crafted digital products.

            You receive rough notes about a client's project (call notes, chat messages, bullet points) and turn them into a clear first draft that noehassiel will review and edit before sending.

            Project types (services noehassiel offers). Pick the one that fits best for "project_type"; use custom only when none does:
            {$services}

            Writing rules:
            - Write everything in Mexican Spanish, addressed to the client as "tú". Technical terms in English are fine when natural (landing page, backend, deploy).
            - Voice: a friend who knows what they're talking about. Direct, warm, no corporate-speak. Never use "sinergias", "soluciones escalables", "apalancar" or similar buzzwords.
            - Use "nosotros" for the collaboration and "yo" when owning expertise.
            - "what_we_do": what the client gets and why it matters to their business, 1 to 3 short paragraphs.
            - "how_we_do_it": the approach and process, 1 to 3 short paragraphs.
            - Only include work the notes ask for or clearly imply. Put reasonable things that are NOT covered in "excludes" so expectations are clear (e.g. content writing, photography, paid ads, hosting costs), but only when relevant.
            - Stages: 3 to 6, in order, each with a realistic duration like "1 semana" or "2-3 semanas".
            - Items are the billable line items. Give each a clear description and a quantity (usually 1). Never include prices or amounts anywhere: noehassiel sets every price.
            - Never invent facts about the client (numbers, names, history) that are not in the notes.
            - "open_questions": things missing from the notes that noehassiel should confirm with the client before sending. Empty if nothing important is missing.
            PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Short project title, e.g. "Sitio web corporativo para Grupo Innovar".')->required(),
            'project_type' => $schema->string()->enum(array_map(fn (ProposalProjectType $type): string => $type->value, ProposalProjectType::cases()))->required(),
            'what_we_do' => $schema->string()->required(),
            'how_we_do_it' => $schema->string()->required(),
            'includes' => $schema->array()->items($schema->string())->required(),
            'excludes' => $schema->array()->items($schema->string())->required(),
            'stages' => $schema->array()->items($schema->object(fn ($schema) => [
                'name' => $schema->string()->required(),
                'description' => $schema->string()->required(),
                'duration' => $schema->string()->required(),
            ]))->required(),
            'items' => $schema->array()->items($schema->object(fn ($schema) => [
                'description' => $schema->string()->required(),
                'quantity' => $schema->number()->min(1)->required(),
            ]))->required(),
            'open_questions' => $schema->array()->items($schema->string())->required(),
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal client for Linear's GraphQL API: one team, projects and issues.
 */
class Linear
{
    private const ENDPOINT = 'https://api.linear.app/graphql';

    public function isConfigured(): bool
    {
        return filled(config('services.linear.api_key')) && filled(config('services.linear.team_id'));
    }

    /**
     * @return array{id: string, url: string}
     */
    public function createProject(string $name, string $content): array
    {
        return $this->mutate('projectCreate', 'project { id url }', [
            'name' => mb_substr($name, 0, 80),
            'teamIds' => [config('services.linear.team_id')],
            'content' => $content,
        ])['project'];
    }

    /**
     * @return array{id: string, identifier: string, url: string}
     */
    public function createIssue(string $title, string $description, ?string $projectId = null): array
    {
        return $this->mutate('issueCreate', 'issue { id identifier url }', array_filter([
            'teamId' => config('services.linear.team_id'),
            'projectId' => $projectId,
            'title' => $title,
            'description' => $description,
        ]))['issue'];
    }

    /**
     * Teams the API key can see, to find the LINEAR_TEAM_ID.
     *
     * @return array<int, array{id: string, key: string, name: string}>
     */
    public function teams(): array
    {
        $response = Http::withHeaders(['Authorization' => config('services.linear.api_key')])
            ->timeout(15)
            ->post(self::ENDPOINT, ['query' => '{ teams { nodes { id key name } } }']);

        if (! $response->successful() || $response->json('errors')) {
            throw new RuntimeException('Linear teams query failed: '.($response->json('errors.0.message') ?? $response->status()));
        }

        return $response->json('data.teams.nodes');
    }

    /**
     * Run a "<name>(input: ...) { success ... }" mutation and return its payload, or throw so queued jobs retry.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function mutate(string $name, string $fields, array $input): array
    {
        $inputType = ucfirst(str_replace('Create', '', $name)).'CreateInput';

        // Personal API keys go in the header as-is, without "Bearer"
        $response = Http::withHeaders(['Authorization' => config('services.linear.api_key')])
            ->timeout(15)
            ->post(self::ENDPOINT, [
                'query' => "mutation(\$input: {$inputType}!) { {$name}(input: \$input) { success {$fields} } }",
                'variables' => ['input' => $input],
            ]);

        $payload = $response->json("data.{$name}");

        if (! $response->successful() || $response->json('errors') || ! ($payload['success'] ?? false)) {
            throw new RuntimeException("Linear {$name} failed: ".($response->json('errors.0.message') ?? $response->status()));
        }

        return $payload;
    }

    /**
     * Linear signs webhook bodies with HMAC-SHA256 of the raw body using the webhook's secret.
     */
    public static function hasValidSignature(string $body, ?string $signature): bool
    {
        $secret = config('services.linear.webhook_secret');

        return filled($secret) && filled($signature)
            && hash_equals(hash_hmac('sha256', $body, $secret), $signature);
    }
}

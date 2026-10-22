<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GitHubContributions
{
    public const CACHE_KEY = 'github.contributions.v2';

    private const DAYS = 52;

    /**
     * Recent contribution days plus quick stats, cached so page views never hit GitHub.
     *
     * @return array{today: int, week: int, streak: int, days: list<int>}|null Each day is a 0–4 intensity level, oldest first
     */
    public function recent(): ?array
    {
        if (! config('services.github.token') || ! config('services.github.username')) {
            return null;
        }

        return Cache::flexible(self::CACHE_KEY, [now()->addHours(1), now()->addHours(6)], fn (): ?array => $this->fetch());
    }

    /**
     * @return array{today: int, week: int, streak: int, days: list<int>}|null
     */
    private function fetch(): ?array
    {
        $response = Http::withToken(config('services.github.token'))
            ->timeout(5)
            ->post('https://api.github.com/graphql', [
                'query' => 'query($login: String!) { user(login: $login) { contributionsCollection { contributionCalendar { weeks { contributionDays { contributionCount contributionLevel } } } } } }',
                'variables' => ['login' => config('services.github.username')],
            ]);

        $weeks = $response->json('data.user.contributionsCollection.contributionCalendar.weeks');

        if (! $response->successful() || ! $weeks) {
            return null;
        }

        $levels = ['NONE' => 0, 'FIRST_QUARTILE' => 1, 'SECOND_QUARTILE' => 2, 'THIRD_QUARTILE' => 3, 'FOURTH_QUARTILE' => 4];
        $days = collect($weeks)->flatMap(fn (array $week): array => $week['contributionDays']);
        $counts = $days->pluck('contributionCount')->map(fn ($count): int => (int) $count)->reverse()->values();

        // A quiet today doesn't break the streak yet
        $streak = 0;
        foreach ($counts->slice($counts->first() === 0 ? 1 : 0) as $count) {
            if ($count === 0) {
                break;
            }
            $streak++;
        }

        return [
            'today' => $counts->first() ?? 0,
            'week' => (int) collect(end($weeks)['contributionDays'])->sum('contributionCount'),
            'streak' => $streak,
            'days' => $days->take(-self::DAYS)->map(fn (array $day): int => $levels[$day['contributionLevel']] ?? 0)->values()->all(),
        ];
    }
}

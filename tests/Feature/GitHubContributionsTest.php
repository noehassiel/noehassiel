<?php

use App\Services\GitHubContributions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.github.token' => 'test-token', 'services.github.username' => 'noehassiel']);
    Cache::forget(GitHubContributions::CACHE_KEY);
});

/**
 * @param  list<int>  $counts  Daily counts, oldest first
 */
function fakeCalendar(array $counts): array
{
    $days = array_map(fn (int $count): array => ['contributionCount' => $count, 'contributionLevel' => $count ? 'THIRD_QUARTILE' : 'NONE'], $counts);

    return ['data' => ['user' => ['contributionsCollection' => ['contributionCalendar' => [
        'weeks' => array_map(fn (array $week): array => ['contributionDays' => $week], array_chunk($days, 7)),
    ]]]]];
}

it('renders today, week, streak and the last 52 days in the dock', function () {
    // 60 days: a gap 5 days ago, then a run of 4 active days ending yesterday, quiet today
    $counts = [...array_fill(0, 54, 1), 0, 2, 3, 1, 4, 0];
    Http::fake(['api.github.com/*' => Http::response(fakeCalendar($counts))]);

    expect(app(GitHubContributions::class)->recent())->toMatchArray(['today' => 0, 'week' => 8, 'streak' => 4]);

    $html = $this->get('/')->assertSuccessful()->assertSee('dock-github', false)->getContent();

    expect(substr_count($html, 'data-level='))->toBe(52);
});

it('caches the calendar so visits never reach github', function () {
    Http::fake(['api.github.com/*' => Http::response(fakeCalendar(array_fill(0, 60, 1)))]);

    $this->get('/');
    $this->get('/');

    Http::assertSentCount(1);
});

it('hides the widget when github fails or no token is set', function (?string $token) {
    config(['services.github.token' => $token]);
    Http::fake(['api.github.com/*' => Http::response([], 500)]);

    $this->get('/')->assertSuccessful()->assertDontSee('dock-github', false);
})->with([null, 'test-token']);

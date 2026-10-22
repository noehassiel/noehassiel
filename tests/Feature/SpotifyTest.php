<?php

use App\Http\Controllers\SpotifyController;
use App\Models\SpotifyAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.spotify.client_id' => 'client',
        'services.spotify.client_secret' => 'secret',
    ]);
});

it('reports nothing playing before the account is connected', function () {
    Http::fake();

    $this->getJson(route('spotify.now-playing'))
        ->assertSuccessful()
        ->assertExactJson(['playing' => false, 'track' => null]);

    Http::assertNothingSent();
});

it('returns the current track and keeps a rotated refresh token', function () {
    SpotifyAccount::factory()->create(['refresh_token' => 'old-refresh']);

    Http::fake([
        'accounts.spotify.com/api/token' => Http::response(['access_token' => 'access', 'expires_in' => 3600, 'refresh_token' => 'new-refresh']),
        'api.spotify.com/v1/me/player/currently-playing' => Http::response([
            'is_playing' => true,
            'progress_ms' => 1000,
            'item' => [
                'type' => 'track',
                'name' => "God's Plan",
                'duration_ms' => 198000,
                'artists' => [['name' => 'Drake']],
                'album' => ['images' => [['url' => 'https://i.scdn.co/640', 'width' => 640], ['url' => 'https://i.scdn.co/300', 'width' => 300], ['url' => 'https://i.scdn.co/64', 'width' => 64]]],
                'external_urls' => ['spotify' => 'https://open.spotify.com/track/abc'],
            ],
        ]),
    ]);

    $this->getJson(route('spotify.now-playing'))
        ->assertSuccessful()
        ->assertJsonPath('playing', true)
        ->assertJsonPath('track.title', "God's Plan")
        ->assertJsonPath('track.artist', 'Drake')
        ->assertJsonPath('track.cover', 'https://i.scdn.co/300');

    expect(SpotifyAccount::sole()->refresh_token)->toBe('new-refresh');
});

it('reports nothing playing when spotify returns no content', function () {
    SpotifyAccount::factory()->create();

    Http::fake([
        'accounts.spotify.com/api/token' => Http::response(['access_token' => 'access', 'expires_in' => 3600]),
        'api.spotify.com/*' => Http::response(null, 204),
    ]);

    $this->getJson(route('spotify.now-playing'))->assertJsonPath('playing', false);
});

it('connects spotify through the link printed by the command', function () {
    Http::fake([
        'accounts.spotify.com/api/token' => Http::response(['access_token' => 'access', 'expires_in' => 3600, 'refresh_token' => 'refresh']),
    ]);

    $this->artisan('spotify:connect')->assertSuccessful();

    $this->get(route('spotify.callback', ['code' => 'code', 'state' => Cache::get(SpotifyController::CONNECT_STATE_KEY)]))
        ->assertRedirect(route('home'));

    expect(SpotifyAccount::sole()->refresh_token)->toBe('refresh');
});

it('rejects a callback with a forged state', function () {
    Cache::put(SpotifyController::CONNECT_STATE_KEY, 'expected');

    $this->get(route('spotify.callback', ['code' => 'code', 'state' => 'forged']))->assertForbidden();

    expect(SpotifyAccount::count())->toBe(0);
});

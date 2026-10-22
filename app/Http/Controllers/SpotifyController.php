<?php

namespace App\Http\Controllers;

use App\Models\SpotifyAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SpotifyController extends Controller
{
    private const NOW_PLAYING_KEY = 'spotify.now-playing';

    private const ACCESS_TOKEN_KEY = 'spotify.access-token';

    public const CONNECT_STATE_KEY = 'spotify.connect-state';

    /**
     * Must match a redirect URI registered in the Spotify dashboard exactly.
     */
    public static function redirectUri(): string
    {
        return config('services.spotify.redirect_uri') ?: route('spotify.callback');
    }

    public function callback(Request $request): RedirectResponse
    {
        // Only a link printed by `php artisan spotify:connect` carries a valid state
        abort_unless(
            $request->filled('code') && hash_equals((string) Cache::pull(self::CONNECT_STATE_KEY), (string) $request->query('state')),
            403,
        );

        $tokens = $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $request->query('code'),
            'redirect_uri' => self::redirectUri(),
        ]);

        abort_unless(isset($tokens['refresh_token']), 502);

        $this->storeRefreshToken($tokens['refresh_token']);
        Cache::put(self::ACCESS_TOKEN_KEY, $tokens['access_token'], now()->addSeconds($tokens['expires_in'] - 60));
        Cache::forget(self::NOW_PLAYING_KEY);

        return redirect()->route('home');
    }

    /**
     * Public and cached briefly, so visitor traffic never reaches Spotify's rate limits.
     */
    public function nowPlaying(): JsonResponse
    {
        $track = Cache::remember(self::NOW_PLAYING_KEY, now()->addSeconds(10), fn (): array => $this->fetchNowPlaying());

        return response()->json(['playing' => $track !== [], 'track' => $track ?: null]);
    }

    /**
     * @return array{title?: string, artist?: string, cover?: ?string, url?: string, progress_ms?: int, duration_ms?: int}
     */
    private function fetchNowPlaying(): array
    {
        $accessToken = $this->accessToken();

        if (! $accessToken) {
            return [];
        }

        $response = Http::withToken($accessToken)->timeout(5)->get('https://api.spotify.com/v1/me/player/currently-playing');

        $item = $response->json('item');

        // 204 means nothing is playing; podcasts and ads come without a track item
        if (! $response->successful() || ! $response->json('is_playing') || ($item['type'] ?? null) !== 'track') {
            return [];
        }

        return [
            'title' => $item['name'],
            'artist' => collect($item['artists'])->pluck('name')->join(', '),
            'cover' => collect($item['album']['images'] ?? [])->sortBy('width')->firstWhere('width', '>=', 100)['url'] ?? null,
            'url' => $item['external_urls']['spotify'],
            'progress_ms' => (int) $response->json('progress_ms'),
            'duration_ms' => (int) $item['duration_ms'],
        ];
    }

    private function accessToken(): ?string
    {
        if ($cached = Cache::get(self::ACCESS_TOKEN_KEY)) {
            return $cached;
        }

        $account = SpotifyAccount::first();

        if (! $account) {
            return null;
        }

        $tokens = $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);

        if (! isset($tokens['access_token'])) {
            return null;
        }

        // Spotify may rotate the refresh token; keep the newest one
        if (isset($tokens['refresh_token'])) {
            $this->storeRefreshToken($tokens['refresh_token']);
        }

        Cache::put(self::ACCESS_TOKEN_KEY, $tokens['access_token'], now()->addSeconds($tokens['expires_in'] - 60));

        return $tokens['access_token'];
    }

    /**
     * @param  array<string, string>  $payload
     * @return array<string, mixed>
     */
    private function requestToken(array $payload): array
    {
        return Http::asForm()
            ->withBasicAuth(config('services.spotify.client_id'), config('services.spotify.client_secret'))
            ->timeout(5)
            ->post('https://accounts.spotify.com/api/token', $payload)
            ->json() ?? [];
    }

    private function storeRefreshToken(string $refreshToken): void
    {
        SpotifyAccount::query()->firstOrNew()->fill(['refresh_token' => $refreshToken])->save();
    }
}

<?php

namespace App\Console\Commands;

use App\Http\Controllers\SpotifyController;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[Signature('spotify:connect')]
#[Description('Print a one-time link that connects your Spotify account to the dock player')]
class SpotifyConnect extends Command
{
    /**
     * Whoever can run artisan owns the site, so the one-time state replaces logging in.
     */
    public function handle(): int
    {
        $state = Str::random(40);
        Cache::put(SpotifyController::CONNECT_STATE_KEY, $state, now()->addMinutes(10));

        $this->info('Open this link within 10 minutes and click Agree:');
        $this->newLine();
        $this->line('https://accounts.spotify.com/authorize?'.http_build_query([
            'client_id' => config('services.spotify.client_id'),
            'response_type' => 'code',
            'redirect_uri' => SpotifyController::redirectUri(),
            'scope' => 'user-read-currently-playing',
            'state' => $state,
        ]));

        return self::SUCCESS;
    }
}

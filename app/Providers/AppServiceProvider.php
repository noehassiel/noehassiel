<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every OS1 request costs LLM and TTS money; cap it per visitor
        RateLimiter::for('os1', fn (Request $request): array => [
            Limit::perMinute(config('services.os1.requests_per_minute') * 2)->by($request->ip()),
            Limit::perDay(config('services.os1.requests_per_day'))->by('day:'.$request->ip()),
        ]);
    }
}

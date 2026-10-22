<?php

namespace App\Providers;

use App\Http\Middleware\UseUserLocale;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Gate::define('access-admin', fn (User $user): bool => $user->email === config('app.admin_email'));
        Gate::define('access-portal', fn (User $user): bool => $user->isClient());
        Gate::define('view-portal-invoices', fn (User $user): bool => $user->canSeeInvoices());
        Gate::define('manage-portal-team', fn (User $user): bool => $user->isPortalOwner());

        // Livewire re-applies this on /livewire/update so every action keeps the user's language
        Livewire::addPersistentMiddleware([UseUserLocale::class]);

        // Every OS1 request costs LLM and TTS money; cap it per visitor
        RateLimiter::for('os1', fn (Request $request): array => [
            Limit::perMinute(config('services.os1.requests_per_minute') * 2)->by($request->ip()),
            Limit::perDay(config('services.os1.requests_per_day'))->by('day:'.$request->ip()),
        ]);
    }
}

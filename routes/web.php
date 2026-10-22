<?php

use App\Http\Controllers\Os1Controller;
use App\Http\Controllers\SpotifyController;
use App\Http\Middleware\UsePortalLocale;
use App\Livewire\Client\Index as ClientIndex;
use App\Livewire\Client\Show as ClientShow;
use App\Livewire\Portal\AcceptInvitation;
use App\Livewire\Portal\Home as PortalHome;
use App\Livewire\Proposal\Create as ProposalCreate;
use App\Livewire\Proposal\Edit as ProposalEdit;
use App\Livewire\Proposal\Index as ProposalIndex;
use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use App\Models\Invoice;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::namespace('App\Http\Controllers')->group(
    function () {
        Route::get('/', 'FrontController@index')->name('home');
    }
);

Route::prefix('os1')->name('os1.')->group(function () {
    // Only replies cost money per request, so only they are rate limited; audio URLs are minted by replies or fixed
    Route::post('reply', [Os1Controller::class, 'reply'])->middleware('throttle:os1')->name('reply');
    Route::get('speech', [Os1Controller::class, 'speech'])->name('speech');

    // No session or cookies, so Cloudflare can cache the fixed lines at the edge
    Route::get('voice/{line}-{language}-{hash}.mp3', [Os1Controller::class, 'voice'])
        ->where(['line' => '[a-z]+', 'language' => 'en|es', 'hash' => '[a-f0-9]{12}'])
        ->withoutMiddleware([
            StartSession::class,
            ShareErrorsFromSession::class,
            AddQueuedCookiesToResponse::class,
            PreventRequestForgery::class,
            EncryptCookies::class,
        ])
        ->name('voice');
});

Route::get('spotify/now-playing', [SpotifyController::class, 'nowPlaying'])
    ->middleware('throttle:30,1')
    ->name('spotify.now-playing');

Route::get('spotify/callback', [SpotifyController::class, 'callback'])
    ->middleware('throttle:10,1')
    ->name('spotify.callback');

// Where every login lands (Fortify "home"): the admin goes to the dashboard, client users to the portal
Route::get('home', function () {
    $user = auth()->user();

    return match (true) {
        Gate::allows('access-admin') => redirect()->route('dashboard'),
        $user->isClient() => redirect()->route('portal.home'),
        default => abort(403),
    };
})->middleware('auth')->name('home.redirect');

// The emailed link is the credential, so whoever received the invoice can download it without an account
Route::get('invoices/{invoice}/download', function (Invoice $invoice) {
    abort_unless($invoice->hasZip(), 404);

    return Invoice::disk()->download($invoice->zip_path, "factura-{$invoice->number}.zip");
})->middleware(['signed', 'throttle:30,1'])->name('invoices.download');

Route::get('invitations/{token}', AcceptInvitation::class)
    ->middleware(['guest', 'throttle:20,1'])
    ->name('invitations.accept');

Route::prefix('portal')->name('portal.')->middleware(['auth', 'can:access-portal', UsePortalLocale::class])->group(function () {
    Route::get('/', PortalHome::class)->name('home');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'can:access-admin'])
    ->name('dashboard');

Route::middleware(['auth', 'can:access-admin'])->group(function () {

    Route::get('clients', ClientIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show');

    Route::get('proposals', ProposalIndex::class)->name('proposals.index');
    Route::get('proposals/create', ProposalCreate::class)->name('proposals.create');
    Route::get('proposals/{proposal}', ProposalShow::class)->name('proposals.show');
    Route::get('proposals/{proposal}/edit', ProposalEdit::class)->name('proposals.edit');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::redirect('settings/password', 'settings/security');
    Route::get('settings/security', Security::class)->middleware('password.confirm')->name('settings.security');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

// Lets password managers deep-link to where passkeys are created and managed
Route::get('.well-known/passkey-endpoints', fn () => response()->json([
    'enroll' => route('settings.security'),
    'manage' => route('settings.security'),
]))->name('well-known.passkeys');

require __DIR__.'/auth.php';

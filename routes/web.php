<?php

use App\Http\Controllers\LinearWebhookController;
use App\Http\Controllers\Os1Controller;
use App\Http\Controllers\SpotifyController;
use App\Http\Middleware\UseUserLocale;
use App\Livewire\Client\Index as ClientIndex;
use App\Livewire\Client\Show as ClientShow;
use App\Livewire\Dashboard;
use App\Livewire\Lead\Index as LeadIndex;
use App\Livewire\Lead\Show as LeadShow;
use App\Livewire\Portal\AcceptInvitation;
use App\Livewire\Portal\Home as PortalHome;
use App\Livewire\Portal\Invoices as PortalInvoices;
use App\Livewire\Portal\ProjectShow as PortalProjectShow;
use App\Livewire\Portal\Security as PortalSecurity;
use App\Livewire\Portal\Team as PortalTeam;
use App\Livewire\Project\Show as ProjectShow;
use App\Livewire\Proposal\Create as ProposalCreate;
use App\Livewire\Proposal\Edit as ProposalEdit;
use App\Livewire\Proposal\Index as ProposalIndex;
use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Quote\Show as QuoteShow;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use App\Models\Invoice;
use App\Models\RequirementFile;
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

// Both the admin and the client team download what the client uploaded to a deliverable
Route::get('requirement-files/{file}/download', function (RequirementFile $file) {
    Gate::authorize('view', $file->requirement->project);

    abort_unless(RequirementFile::disk()->exists($file->path), 404);

    return RequirementFile::disk()->download($file->path, $file->original_name);
})->middleware(['auth', 'throttle:60,1'])->name('requirement-files.download');

Route::get('invitations/{token}', AcceptInvitation::class)
    ->middleware(['guest', 'throttle:20,1'])
    ->name('invitations.accept');

// Linear signs each delivery; the controller verifies it
Route::post('linear/webhook', LinearWebhookController::class)->middleware('throttle:120,1')->name('linear.webhook');

// Shareable proposal page: the link plus its password are the credential, no account needed
Route::get('q/{token}', QuoteShow::class)
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:60,1')
    ->name('quotes.show');

Route::prefix('portal')->name('portal.')->middleware(['auth', 'can:access-portal', UseUserLocale::class])->group(function () {
    Route::get('/', PortalHome::class)->name('home');
    Route::get('projects/{project}', PortalProjectShow::class)->name('projects.show');
    Route::get('invoices', PortalInvoices::class)->middleware('can:view-portal-invoices')->name('invoices');
    Route::get('team', PortalTeam::class)->middleware('can:manage-portal-team')->name('team');
    Route::get('security', PortalSecurity::class)->middleware('password.confirm')->name('security');

    Route::get('invoices/{invoice}/download', function (Invoice $invoice) {
        abort_unless($invoice->hasZip(), 404);

        return Invoice::disk()->download($invoice->zip_path, "factura-{$invoice->number}.zip");
    })->middleware('can:download,invoice')->name('invoices.download');
});

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified', 'can:access-admin', UseUserLocale::class])
    ->name('dashboard');

Route::middleware(['auth', 'can:access-admin', UseUserLocale::class])->group(function () {

    Route::get('clients', ClientIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show');

    Route::get('leads', LeadIndex::class)->name('leads.index');
    Route::get('leads/{lead}', LeadShow::class)->name('leads.show');

    Route::get('projects/{project}', ProjectShow::class)->name('projects.show');

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
    'enroll' => route('security.redirect'),
    'manage' => route('security.redirect'),
]))->name('well-known.passkeys');

// Each kind of user manages passkeys on their own security page
Route::get('account/security', fn () => redirect()->route(
    auth()->user()->isClient() ? 'portal.security' : 'settings.security'
))->middleware('auth')->name('security.redirect');

require __DIR__.'/auth.php';

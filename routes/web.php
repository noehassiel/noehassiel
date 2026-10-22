<?php

use App\Http\Controllers\Os1Controller;
use App\Http\Controllers\SpotifyController;
use App\Livewire\Client\Index as ClientIndex;
use App\Livewire\Client\Show as ClientShow;
use App\Livewire\Proposal\Create as ProposalCreate;
use App\Livewire\Proposal\Edit as ProposalEdit;
use App\Livewire\Proposal\Index as ProposalIndex;
use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::namespace('App\Http\Controllers')->group(
    function () {
        Route::get('/', 'FrontController@index')->name('home');
    }
);

Route::prefix('os1')->name('os1.')->middleware('throttle:os1')->group(function () {
    Route::get('greeting', [Os1Controller::class, 'greeting'])->name('greeting');
    Route::get('line/{key}', [Os1Controller::class, 'line'])->name('line');
    Route::post('reply', [Os1Controller::class, 'reply'])->name('reply');
});

Route::get('spotify/now-playing', [SpotifyController::class, 'nowPlaying'])
    ->middleware('throttle:30,1')
    ->name('spotify.now-playing');

Route::get('spotify/callback', [SpotifyController::class, 'callback'])
    ->middleware('throttle:10,1')
    ->name('spotify.callback');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('clients', ClientIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show');

    Route::get('invoices/{invoice}/download', function (Invoice $invoice) {
        abort_unless(request()->hasValidSignature(), 403);
        abort_unless($invoice->zip_path && Storage::disk('local')->exists($invoice->zip_path), 404);

        return Storage::disk('local')->download(
            $invoice->zip_path,
            "factura-{$invoice->number}.zip"
        );
    })->name('invoices.download');

    Route::get('proposals', ProposalIndex::class)->name('proposals.index');
    Route::get('proposals/create', ProposalCreate::class)->name('proposals.create');
    Route::get('proposals/{proposal}', ProposalShow::class)->name('proposals.show');
    Route::get('proposals/{proposal}/edit', ProposalEdit::class)->name('proposals.edit');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';

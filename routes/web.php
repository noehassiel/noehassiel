<?php

use App\Livewire\Client\Index as ClientIndex;
use App\Livewire\Client\Show as ClientShow;
use App\Livewire\Proposal\Create as ProposalCreate;
use App\Livewire\Proposal\Edit as ProposalEdit;
use App\Livewire\Proposal\Index as ProposalIndex;
use App\Livewire\Proposal\Show as ProposalShow;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::namespace('App\Http\Controllers')->group(
    function () {
        Route::get('/', 'FrontController@index')->name('home');
    }
);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('clients', ClientIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientShow::class)->name('clients.show');

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

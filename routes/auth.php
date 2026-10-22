<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Auth\VerifyEmail;
use Illuminate\Support\Facades\Route;

// Login, logout, password reset, password confirmation, 2FA and passkeys are registered by Fortify

Route::middleware('auth')->group(function () {
    Route::get('verify-email', VerifyEmail::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

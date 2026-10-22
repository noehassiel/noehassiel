<?php

namespace App\Livewire\Portal;

use App\Livewire\Settings\Security as SettingsSecurity;
use Illuminate\View\View;
use Livewire\Attributes\Layout;

/**
 * Same password, 2FA and passkey management as the admin, inside the portal frame.
 */
#[Layout('components.layouts.portal')]
class Security extends SettingsSecurity
{
    public function render(): View
    {
        return view('livewire.portal.security')->title(__('portal.security.title'));
    }
}

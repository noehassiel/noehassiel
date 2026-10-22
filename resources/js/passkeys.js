import { Passkeys } from '@laravel/passkeys';

// Exposed globally so the Alpine passkey components can reach it
window.Passkeys = Passkeys;
window.dispatchEvent(new CustomEvent('passkeys:ready'));

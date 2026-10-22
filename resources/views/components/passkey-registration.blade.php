<div
    x-data="{
        supported: false,
        showForm: false,
        name: '',
        loading: false,
        error: null,
        updateSupport() {
            this.supported = Boolean(window.Passkeys?.isSupported());
        },
        defaultPasskeyName() {
            const ua = navigator.userAgent;

            const browser = [
                { pattern: /Edg|Edge/, name: 'Edge' },
                { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
                { pattern: /Firefox|FxiOS/, name: 'Firefox' },
                { pattern: /Chrome|CriOS/, name: 'Chrome' },
                { pattern: /Safari/, name: 'Safari' },
            ].find(({ pattern }) => pattern.test(ua))?.name;

            const os = [
                { pattern: /iPhone/, name: 'iPhone' },
                { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
                { pattern: /Android/, name: 'Android' },
                { pattern: /Mac/, name: 'Mac' },
                { pattern: /Windows/, name: 'Windows' },
            ].find(({ pattern }) => pattern.test(ua))?.name;

            return [browser, os].filter(Boolean).join(' on ');
        },
        init() {
            this.name = this.defaultPasskeyName();
            this.updateSupport();
            window.addEventListener('passkeys:ready', () => this.updateSupport(), { once: true });
        },
        async register() {
            if (! this.name.trim()) {
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                await window.Passkeys.register({ name: this.name });
                this.cancel();
                await $wire.loadPasskeys();
            } catch (e) {
                if (e.constructor?.name !== 'UserCancelledError') {
                    this.error = e.message;
                }
            } finally {
                this.loading = false;
            }
        },
        cancel() {
            this.showForm = false;
            this.name = this.defaultPasskeyName();
            this.error = null;
        },
    }"
>
    <template x-if="! supported">
        <x-ui.text class="text-zinc-500">{{ __('Passkeys are not supported in this browser.') }}</x-ui.text>
    </template>

    <template x-if="supported && ! showForm">
        <x-ui.button variant="primary" icon="plus" x-on:click="showForm = true">
            {{ __('Add passkey') }}
        </x-ui.button>
    </template>

    <template x-if="supported && showForm">
        <div class="space-y-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
            <x-ui.field>
                <x-ui.label>{{ __('Passkey name') }}</x-ui.label>
                <x-ui.input
                    x-model="name"
                    :placeholder="__('e.g., MacBook Pro, iPhone')"
                    x-on:keydown.enter.prevent="register()"
                    x-init="$nextTick(() => $el.focus())"
                />
                <x-ui.description>{{ __('Give this passkey a name to help you identify it later.') }}</x-ui.description>
            </x-ui.field>

            <p x-show="error" x-text="error" x-cloak class="text-sm text-red-600 dark:text-red-400"></p>

            <div class="flex gap-2">
                <x-ui.button variant="primary" x-on:click="register()" x-bind:disabled="loading || ! name.trim()">
                    <span x-show="! loading">{{ __('Register passkey') }}</span>
                    <span x-show="loading" x-cloak>{{ __('Registering...') }}</span>
                </x-ui.button>
                <x-ui.button variant="ghost" x-on:click="cancel()">{{ __('Cancel') }}</x-ui.button>
            </div>
        </div>
    </template>
</div>

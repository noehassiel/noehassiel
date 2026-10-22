<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="admin min-h-screen">
        <x-ui.layout>
            <x-ui.sidebar>
                <x-slot:brand>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3" wire:navigate>
                        <img src="{{ asset('logo/isotipo_dark_ember.svg') }}" alt="noehassiel" class="size-8 shrink-0">
                        <span data-slot="brand-name" class="leading-none">
                            <span class="block text-[15px] font-bold tracking-tight">noehassiel</span>
                            <span class="mt-1 block font-mono text-[10px] tracking-[0.12em] text-[var(--ember)]">artisan developer</span>
                        </span>
                    </a>
                </x-slot:brand>

                <x-ui.navlist>
                    <x-ui.navlist.group label="{{ __('Platform') }}">
                        <x-ui.navlist.item
                            icon="squares-2x2"
                            :href="route('dashboard')"
                            :active="request()->routeIs('dashboard')"
                            label="{{ __('Dashboard') }}"
                            wire:navigate
                        />
                    </x-ui.navlist.group>

                    <x-ui.navlist.group label="{{ __('Business') }}">
                        <x-ui.navlist.item
                            icon="sparkles"
                            :href="route('leads.index')"
                            :active="request()->routeIs('leads.*')"
                            label="{{ __('Leads') }}"
                            wire:navigate
                        />
                        <x-ui.navlist.item
                            icon="user-group"
                            :href="route('clients.index')"
                            :active="request()->routeIs('clients.*')"
                            label="{{ __('Clientes') }}"
                            wire:navigate
                        />
                        <x-ui.navlist.item
                            icon="document-text"
                            :href="route('proposals.index')"
                            :active="request()->routeIs('proposals.*')"
                            label="{{ __('Proposals') }}"
                            wire:navigate
                        />
                    </x-ui.navlist.group>
                </x-ui.navlist>

                <div class="mt-auto p-2">
                    <x-ui.dropdown>
                        <x-slot:button>
                            <x-ui.button variant="ghost" class="w-full justify-start gap-2 rounded-[var(--radius-box)] border border-[#1C1C1C] px-2 py-2">
                                <x-ui.avatar
                                    :initials="auth()->user()->initials()"
                                    size="sm"
                                />
                                <div class="flex-1 text-start text-sm leading-tight [:has([data-collapsed]_&)_&]:hidden">
                                    <span class="block font-semibold truncate">{{ auth()->user()->name }}</span>
                                    <span class="block text-xs text-neutral-500 truncate">{{ auth()->user()->email }}</span>
                                </div>
                            </x-ui.button>
                        </x-slot:button>
                        <x-slot:menu>
                            <x-ui.dropdown.item :href="route('settings.profile')" icon="cog-6-tooth" wire:navigate>
                                {{ __('Settings') }}
                            </x-ui.dropdown.item>
                            <x-ui.separator />
                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <x-ui.dropdown.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                    {{ __('Log Out') }}
                                </x-ui.dropdown.item>
                            </form>
                        </x-slot:menu>
                    </x-ui.dropdown>
                </div>
            </x-ui.sidebar>

            <x-ui.layout.main>
                <x-ui.layout.header>
                    <x-ui.sidebar.toggle class="md:hidden" />

                    <div class="grow"></div>

                    <span class="font-mono text-[11px] tracking-[0.18em] text-[var(--slate)] uppercase max-sm:hidden">
                        {{ now()->translatedFormat('D d M') }}
                    </span>
                </x-ui.layout.header>

                <div class="relative z-10 px-4 py-8 sm:px-8 lg:py-10">
                    {{ $slot }}
                </div>
            </x-ui.layout.main>
        </x-ui.layout>
    </body>
</html>

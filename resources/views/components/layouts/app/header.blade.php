<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-neutral-950">
        <x-ui.layout>
            <x-ui.sidebar>
                <x-slot:brand>
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2" wire:navigate>
                        <x-app-logo />
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
                </x-ui.navlist>

                <div class="mt-auto p-2">
                    <x-ui.dropdown>
                        <x-slot:button>
                            <x-ui.button variant="ghost" class="w-full justify-start gap-2 px-2 py-1.5">
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
                    <x-ui.sidebar.toggle />

                    <div class="grow"></div>

                    <x-ui.navbar>
                        <x-ui.tooltip placement="bottom">
                            <x-slot:trigger>
                                <x-ui.navbar.item icon="magnifying-glass" href="#" label="{{ __('Search') }}" />
                            </x-slot:trigger>
                            <x-ui.tooltip.content>{{ __('Search') }}</x-ui.tooltip.content>
                        </x-ui.tooltip>

                        <x-ui.tooltip placement="bottom">
                            <x-slot:trigger>
                                <x-ui.navbar.item
                                    class="max-lg:hidden"
                                    icon="code-bracket-square"
                                    href="https://github.com/laravel/livewire-starter-kit"
                                    target="_blank"
                                    label="{{ __('Repository') }}"
                                />
                            </x-slot:trigger>
                            <x-ui.tooltip.content>{{ __('Repository') }}</x-ui.tooltip.content>
                        </x-ui.tooltip>

                        <x-ui.tooltip placement="bottom">
                            <x-slot:trigger>
                                <x-ui.navbar.item
                                    class="max-lg:hidden"
                                    icon="book-open"
                                    href="https://laravel.com/docs/starter-kits"
                                    target="_blank"
                                    label="{{ __('Documentation') }}"
                                />
                            </x-slot:trigger>
                            <x-ui.tooltip.content>{{ __('Documentation') }}</x-ui.tooltip.content>
                        </x-ui.tooltip>
                    </x-ui.navbar>
                </x-ui.layout.header>

                <div class="p-6">
                    {{ $slot }}
                </div>
            </x-ui.layout.main>
        </x-ui.layout>
    </body>
</html>

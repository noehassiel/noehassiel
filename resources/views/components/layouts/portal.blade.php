<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-neutral-950">
        <header class="border-b border-neutral-200 dark:border-neutral-800">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
                <a href="{{ route('portal.home') }}" class="flex items-center gap-3" wire:navigate>
                    <x-app-logo-icon class="size-7 fill-current text-black dark:text-white" />
                    <span class="truncate text-sm font-semibold">{{ auth()->user()->client->name }}</span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-neutral-500 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle">
                            {{ __('Log Out') }}
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
            {{ $slot }}
        </main>
    </body>
</html>

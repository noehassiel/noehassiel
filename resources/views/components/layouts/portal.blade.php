<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="portal">
        @php
            $navItems = [
                ['route' => 'portal.home', 'active' => ['portal.home', 'portal.projects.*'], 'label' => __('portal.nav.projects')],
                ['route' => 'portal.invoices', 'active' => ['portal.invoices'], 'label' => __('portal.nav.invoices')],
                ['route' => 'portal.security', 'active' => ['portal.security'], 'label' => __('portal.nav.security')],
            ];
        @endphp

        <div class="relative z-10">
            <header class="border-b border-[#1C1C1C]">
                <div class="mx-auto flex h-16 max-w-5xl items-center justify-between gap-4 px-4 sm:px-6">
                    <a href="{{ route('portal.home') }}" class="flex min-w-0 items-center gap-3" wire:navigate>
                        <img src="{{ asset('logo/isotipo_dark_ember.svg') }}" alt="noehassiel" class="size-8 shrink-0">
                        <span class="truncate font-bold tracking-tight">{{ auth()->user()->client->name }}</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="portal-eyebrow cursor-pointer transition-colors hover:text-[var(--stone)]">
                            {{ __('portal.nav.logout') }}
                        </button>
                    </form>
                </div>

                <nav aria-label="{{ __('portal.nav.label') }}" class="mx-auto max-w-5xl overflow-x-auto px-4 sm:px-6">
                    <ul class="flex gap-6">
                        @foreach ($navItems as $item)
                            @php($isActive = request()->routeIs(...$item['active']))
                            <li>
                                <a href="{{ route($item['route']) }}" wire:navigate
                                    @if ($isActive) aria-current="page" @endif
                                    @class([
                                        'block border-b-2 py-3 font-mono text-xs uppercase tracking-[0.16em] whitespace-nowrap transition-colors',
                                        'border-[var(--ember)] text-[var(--stone)]' => $isActive,
                                        'border-transparent text-[var(--slate)] hover:text-[var(--stone)]' => ! $isActive,
                                    ])>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </header>

            <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14">
                {{ $slot }}
            </main>

            <footer class="mx-auto flex max-w-5xl items-center justify-between px-4 pb-10 sm:px-6">
                <span class="text-xs font-extrabold tracking-tight">noehassiel</span>
                <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-[var(--ember)]">artisan developer</span>
            </footer>
        </div>
    </body>
</html>

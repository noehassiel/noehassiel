<!DOCTYPE html>
<html lang="es" class="dark">
    <head>
        @include('partials.head')
        {{-- Private by design: the link is a credential, keep it out of search engines --}}
        <meta name="robots" content="noindex, nofollow">
    </head>
    <body class="portal">
        <div class="relative z-10">
            <header class="border-b border-[#1C1C1C]">
                <div class="mx-auto flex h-16 max-w-4xl items-center justify-between gap-4 px-4 sm:px-6">
                    <img src="{{ asset('logo/isotipo_dark_ember.svg') }}" alt="noehassiel" class="size-8 shrink-0">
                    <span class="portal-eyebrow">Propuesta privada</span>
                </div>
            </header>

            <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14">
                {{ $slot }}
            </main>

            <footer class="mx-auto flex max-w-4xl items-center justify-between px-4 pb-10 sm:px-6">
                <span class="text-xs font-extrabold tracking-tight">noehassiel</span>
                <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-[var(--ember)]">artisan developer</span>
            </footer>
        </div>
    </body>
</html>

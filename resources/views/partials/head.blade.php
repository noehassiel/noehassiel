<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? 'noehassiel' }}</title>

@include('partials.favicons')

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/passkeys.js', 'resources/css/system.css'])

{{-- app.js bundles Livewire + Alpine; this stops Livewire injecting a second copy --}}
@livewireScriptConfig

{{-- The brand is dark-only: force dark before render, whatever the OS or a stale saved preference says --}}
<script>
    document.documentElement.classList.add('dark');
</script>

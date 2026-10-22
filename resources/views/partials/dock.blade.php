{{-- Bottom dock: navigation as widgets, Samantha as an app --}}
<nav class="dock" id="dock" aria-label="Main">
    <a href="#top" class="dock-widget dock-brand" aria-label="noehassiel, back to top">
        <span class="brand-mark" aria-hidden="true" data-brand-mark data-brand-mark-compact="(max-width: 940px)"><span data-brand-text>n<span class="brand-slash">/</span>h</span><span class="brand-cursor"></span></span>
    </a>

    <div class="dock-widget dock-clock" aria-hidden="true">
        <span class="dock-clock-time" data-clock>--:--</span>
        <span class="dock-clock-label">Puebla, MX</span>
    </div>

    <button type="button" class="dock-widget dock-samantha" id="os1-toggle"
        aria-expanded="false" aria-controls="os1-frame">
        <span class="dock-samantha-ring" aria-hidden="true"></span>
        <span class="dock-samantha-name">Samantha</span>
        <x-t key="samantha.open" class="sr-only" />
    </button>

    {{-- Filled by dock.js while something is playing on Spotify --}}
    <a class="dock-widget dock-music" id="dock-music" data-now-playing="{{ route('spotify.now-playing') }}"
        target="_blank" rel="noopener" hidden>
        <img class="dock-music-cover" data-music-cover alt="" width="48" height="48">
        <span class="dock-music-meta">
            <span class="dock-music-label" aria-hidden="true">
                <span class="dock-music-bars"><i></i><i></i><i></i><i></i></span>
                <x-t key="music.listening" class="dock-music-label-long" />
                <x-t key="music.listening_short" class="dock-music-label-short" />
            </span>
            <span class="dock-music-title" data-music-title></span>
            <span class="dock-music-artist" data-music-artist></span>
            <span class="dock-music-progress" aria-hidden="true"><span data-music-progress></span></span>
        </span>
        <span class="dock-music-play" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M8 5.5v13l10-6.5z" fill="currentColor" /></svg>
        </span>
    </a>

    <ul class="dock-widget dock-apps">
        @foreach (['work' => '001', 'services' => '002', 'process' => '003', 'about' => '004', 'contact' => '005'] as $section => $number)
            <li>
                <a href="#{{ $section }}" class="dock-app" data-dock-link="{{ $section }}">
                    <span class="dock-app-number" aria-hidden="true">{{ $number }}</span>
                    <x-t key="nav.{{ $section }}" class="dock-app-label" />
                </a>
            </li>
        @endforeach
    </ul>

    <div class="dock-widget dock-language" role="group" aria-label="Language / Idioma">
        <button type="button" data-set-language="en" aria-pressed="true">EN</button>
        <button type="button" data-set-language="es" aria-pressed="false">ES</button>
    </div>
</nav>

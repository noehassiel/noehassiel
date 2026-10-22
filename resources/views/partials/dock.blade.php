{{-- Bottom dock: navigation as widgets, Samantha as an app --}}
<nav class="dock" id="dock" aria-label="Main">
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

<!DOCTYPE html>
<html lang="en" data-lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('landing.meta.title', locale: 'en') }}</title>
    <meta name="description" content="{{ __('landing.meta.description', locale: 'en') }}">
    <meta name="theme-color" content="#FAFAF9">
    <link rel="canonical" href="{{ url('/') }}">
    @include('partials.favicons')

    {{-- Social previews --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="noehassiel">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="{{ __('landing.meta.title', locale: 'en') }}">
    <meta property="og:description" content="{{ __('landing.meta.description', locale: 'en') }}">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="noehassiel — artisan developer">
    <meta property="og:locale" content="en_US">
    <meta property="og:locale:alternate" content="es_MX">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('landing.meta.title', locale: 'en') }}">
    <meta name="twitter:description" content="{{ __('landing.meta.description', locale: 'en') }}">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">

    {{-- Apply the saved language and lock scroll for the intro before first paint --}}
    <script>
        (function () {
            var language = 'en';
            try { language = localStorage.getItem('os1.language') === 'es' ? 'es' : 'en'; } catch (e) {}
            document.documentElement.dataset.lang = language;
            document.documentElement.lang = language;
            document.documentElement.classList.add('os1-intro');

            // Already saw the full intro today: play the quick entrance instead
            try {
                if (localStorage.getItem('os1.introSeen') === new Date().toLocaleDateString('en-CA')) {
                    document.documentElement.classList.add('os1-quick');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css'])
    @livewireStyles
</head>

<body class="landing">
    <a href="#main" class="skip-link"><x-t key="nav.skip" /></a>

    @include('partials.sam')

    <header class="site-header">
        <a href="#top" class="brand" aria-label="noehassiel">
            <span class="brand-mark" aria-hidden="true" data-brand-mark><span data-brand-text>n<span class="brand-slash">/</span>h</span><span class="brand-cursor"></span></span>
            <span class="brand-name">noehassiel</span>
        </a>
        @php
            // Greet in Puebla time, like a friend saying hi from the studio
            $pueblaHour = now('America/Mexico_City')->hour;
            $greetingKey = match (true) {
                $pueblaHour < 12 => 'morning',
                $pueblaHour < 19 => 'afternoon',
                default => 'evening',
            };
        @endphp
        <p class="header-greeting mono"><x-t key="hero.greeting.{{ $greetingKey }}" /></p>
        <a href="#contact" class="button button-small button-light"><x-t key="nav.cta" /></a>
    </header>

    <main id="main">
        {{-- Hero --}}
        <section class="hero" id="top">
            <div class="hero-photo" data-hero-photo>
                {{-- One scene, art-directed: the wide shot on desktop, a tall crop around the subject on phones --}}
                <picture class="hero-figure">
                    <source media="(max-width: 940px)" srcset="{{ asset('images/me/hero-mobile.webp') }}" width="1100" height="1536">
                    <img
                        src="{{ asset('images/me/hero-2560.webp') }}"
                        srcset="{{ asset('images/me/hero-1600.webp') }} 1600w, {{ asset('images/me/hero-2560.webp') }} 2560w"
                        sizes="100vw"
                        width="2560"
                        height="1429"
                        alt="{{ __('landing.hero.photo_alt', locale: 'en') }}"
                        fetchpriority="high"
                        decoding="async"
                    >
                </picture>
            </div>
            <div class="hero-shade" data-hero-shade aria-hidden="true"></div>

            <div class="hero-inner" data-hero-copy>
                <div class="hero-side hero-side-top">
                    <p class="eyebrow">
                        <span class="status-dot" aria-hidden="true"></span>
                        <x-t key="hero.available" />
                    </p>

                    <ul class="hero-services mono">
                        <li><x-t key="services.items.development.title" /></li>
                        <li><x-t key="services.items.design.title" /></li>
                        <li><x-t key="services.items.management.title" /></li>
                    </ul>

                    <a href="#services" class="hero-help">
                        <x-t key="hero.help" />
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 11 11 3M5 3h6v6" /></svg>
                    </a>
                </div>

                <div class="hero-main">
                    <p class="hero-intro">
                        <x-t key="hero.intro" />
                        <span class="hero-name">noe<span>hassiel</span></span>
                    </p>

                    <h1 class="hero-title">
                        <span class="hero-title-line" style="--line: 0"><span><x-t key="hero.title_1" /></span></span>
                        <span class="hero-title-line" style="--line: 1"><span><x-t key="hero.title_2" /></span></span>
                        <span class="hero-title-line hero-title-accent" style="--line: 2"><span><x-t key="hero.title_3" /></span></span>
                    </h1>
                </div>

                <div class="hero-side hero-side-bottom">
                    <p class="hero-lead"><x-t key="hero.lead" /></p>
                    <div class="hero-actions">
                        <a href="#contact" class="button button-small button-primary"><x-t key="hero.primary" /></a>
                        <a href="#work" class="hero-link"><x-t key="hero.secondary" /></a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Work --}}
        <section class="section section-dark" id="work" aria-labelledby="work-title">
            <div class="container">
                <header class="section-header">
                    <p class="section-label mono">001 / <x-t key="work.label" /></p>
                    <h2 class="section-title" id="work-title"><x-t key="work.title" /></h2>
                    <p class="section-lead"><x-t key="work.lead" /></p>
                </header>

                <ol class="work-list">
                    @foreach (config('portfolio.projects') as $project)
                        <li class="work-item">
                            <div class="work-media" aria-hidden="true">
                                @if ($project['image'])
                                    <img src="{{ asset($project['image']) }}" alt="" loading="lazy" width="1600" height="1000">
                                @else
                                    <span class="work-media-empty mono">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                @endif
                            </div>

                            <div class="work-body">
                                <p class="work-meta mono">
                                    <span>{{ $project['year'] }}</span>
                                    <span><span data-lang="en">{{ $project['disciplines']['en'] }}</span><span data-lang="es">{{ $project['disciplines']['es'] }}</span></span>
                                    @if ($project['placeholder'])
                                        <span class="work-placeholder"><x-t key="work.placeholder" /></span>
                                    @endif
                                </p>
                                <h3 class="work-name">{{ $project['name'] }}</h3>
                                <p class="work-client">{{ $project['client'] }}</p>
                                <p class="work-summary"><span data-lang="en">{{ $project['summary']['en'] }}</span><span data-lang="es">{{ $project['summary']['es'] }}</span></p>

                                @if ($project['url'])
                                    <a href="{{ $project['url'] }}" class="work-link" target="_blank" rel="noopener">
                                        <x-t key="work.visit" /> <span aria-hidden="true">↗</span>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Services --}}
        <section class="section" id="services" aria-labelledby="services-title">
            <div class="container">
                <header class="section-header">
                    <p class="section-label mono">002 / <x-t key="services.label" /></p>
                    <h2 class="section-title" id="services-title"><x-t key="services.title" /></h2>
                    <p class="section-lead"><x-t key="services.lead" /></p>
                </header>

                <ol class="service-list">
                    @foreach (['development', 'design', 'management'] as $service)
                        <li class="service-item">
                            <span class="service-number mono" aria-hidden="true">0{{ $loop->iteration }}</span>
                            <h3 class="service-title"><x-t key="services.items.{{ $service }}.title" /></h3>
                            <p class="service-body"><x-t key="services.items.{{ $service }}.body" /></p>
                            <p class="service-tags mono"><x-t key="services.items.{{ $service }}.tags" /></p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        {{-- Process --}}
        <section class="section section-sand" id="process" aria-labelledby="process-title">
            <div class="container">
                <header class="section-header">
                    <p class="section-label mono">003 / <x-t key="process.label" /></p>
                    <h2 class="section-title" id="process-title"><x-t key="process.title" /></h2>
                    <p class="section-lead"><x-t key="process.lead" /></p>
                </header>

                <ol class="process-list">
                    @foreach (['listen', 'design', 'build', 'launch'] as $step)
                        <li class="process-step">
                            <span class="process-number mono" aria-hidden="true">{{ $loop->iteration }}/4</span>
                            <h3 class="process-title"><x-t key="process.steps.{{ $step }}.title" /></h3>
                            <p class="process-body"><x-t key="process.steps.{{ $step }}.body" /></p>
                        </li>
                    @endforeach
                </ol>

                {{-- The commitments a business owner can hold me to --}}
                <div class="process-promises">
                    <p class="process-promises-label mono"><x-t key="promises.label" /></p>
                    <ul>
                        @foreach (['price', 'preview', 'installments', 'bugs'] as $promise)
                            <li><x-t key="promises.items.{{ $promise }}" /></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section class="section" id="about" aria-labelledby="about-title">
            <div class="container about-grid">
                <div class="about-portrait">
                    <img
                        src="{{ asset('images/me/about-960.webp') }}"
                        srcset="{{ asset('images/me/about-640.webp') }} 640w, {{ asset('images/me/about-960.webp') }} 960w"
                        sizes="(min-width: 900px) 40vw, 100vw"
                        alt="{{ __('landing.about.portrait', locale: 'en') }}"
                        width="960"
                        height="1200"
                        loading="lazy"
                        decoding="async"
                    >
                </div>

                <div class="about-copy">
                    <p class="section-label mono">004 / <x-t key="about.label" /></p>
                    <h2 class="section-title" id="about-title"><x-t key="about.title" /></h2>
                    <p class="about-body"><x-t key="about.body_1" /></p>
                    <p class="about-body"><x-t key="about.body_2" /></p>
                    <p class="about-body"><x-t key="about.body_3" :html="true" /></p>

                    <dl class="about-facts">
                        @foreach (['based', 'speaks', 'clients'] as $fact)
                            <div>
                                <dt class="mono"><x-t key="about.facts.{{ $fact }}" /></dt>
                                <dd><x-t key="about.facts.{{ $fact }}_value" /></dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </section>

        {{-- Contact --}}
        <section class="section section-dark" id="contact" aria-labelledby="contact-title">
            <div class="container contact-grid">
                <header class="section-header">
                    <p class="section-label mono">005 / <x-t key="contact.label" /></p>
                    <h2 class="section-title" id="contact-title"><x-t key="contact.title" /></h2>
                    <p class="section-lead"><x-t key="contact.lead" /></p>
                </header>

                <livewire:front.project-form />
            </div>
        </section>
    </main>

    @php($publicEmail = config('app.public_email'))
    @php([$emailUser, $emailDomain] = explode('@', $publicEmail, 2))

    {{-- Footer: the dark contact section glows through ember into stone, with the portrait standing over the address --}}
    <footer class="site-footer">
        <div class="footer-stage">
            <div class="footer-side">
                <nav class="footer-links" aria-label="{{ __('landing.footer.contact', locale: 'en') }}">
                    <p class="footer-links-title"><x-t key="footer.contact" /></p>
                    <ul>
                        <li><a href="mailto:{{ $publicEmail }}"><x-t key="footer.email" /></a></li>
                        <li><a href="#contact"><x-t key="footer.start" /></a></li>
                    </ul>
                </nav>

                <nav class="footer-links" aria-label="{{ __('landing.footer.socials', locale: 'en') }}">
                    <p class="footer-links-title"><x-t key="footer.socials" /></p>
                    <ul>
                        <li><a href="https://github.com/{{ config('services.github.username') }}" target="_blank" rel="noopener">GitHub</a></li>
                    </ul>
                </nav>

                <div class="footer-invite">
                    <p class="footer-ask"><x-t key="footer.ask" /></p>
                    <p class="footer-invite-line"><x-t key="footer.invite" /></p>
                </div>
            </div>

            <div class="footer-belief">
                <p class="footer-belief-lead"><x-t key="footer.belief" /></p>
                <p><x-t key="footer.belief_body" /></p>
            </div>

            <figure class="footer-portrait" aria-hidden="true">
                <img src="{{ asset('images/me/footer-portrait-900.webp') }}"
                    srcset="{{ asset('images/me/footer-portrait-900.webp') }} 413w, {{ asset('images/me/footer-portrait-1800.webp') }} 826w"
                    sizes="(max-width: 900px) 60vw, 28vw" width="413" height="900" alt="" loading="lazy" decoding="async">
            </figure>

            <a class="footer-email" href="mailto:{{ $publicEmail }}">
                <span class="sr-only"><x-t key="footer.write" /> </span>{{ $emailUser }}<span class="footer-email-at">@</span>{{ $emailDomain }}
            </a>

            <div class="footer-meta mono">
                <p aria-hidden="true">$ php artisan make:something --with-intention</p>
                <p>© {{ now()->year }} noehassiel · <x-t key="footer.rights" /></p>
            </div>
        </div>
    </footer>

    @include('partials.dock')

    <script>
        (function () {
            const originalTitle = document.title;
            // Background tabs throttle timers to ~1s, so frames are paced at that rate
            const sleepFrames = ['z', 'zZ', 'zZz', 'zZzZ', 'zZzZz...'];
            let frameIndex = 0;
            let sleepTimer = null;

            // Favicon cursor blink: build an "off" frame by painting over the isotipo cursor
            const iconLinks = Array.from(document.querySelectorAll('link[rel="icon"]'));
            const originalIconHrefs = iconLinks.map((link) => link.href);
            let iconOnHref = null;
            let iconOffHref = null;
            let isCursorVisible = true;

            const iconImage = new Image();
            iconImage.onload = function () {
                const size = 32;
                const canvas = document.createElement('canvas');
                canvas.width = size;
                canvas.height = size;
                const context = canvas.getContext('2d');
                context.drawImage(iconImage, 0, 0, size, size);
                iconOnHref = canvas.toDataURL('image/png');

                // Sample the background so the cover matches the icon exactly
                const [red, green, blue] = context.getImageData(size / 2, 2, 1, 1).data;
                context.fillStyle = `rgb(${red}, ${green}, ${blue})`;
                context.fillRect(26, 11, 5, 10);
                iconOffHref = canvas.toDataURL('image/png');
            };
            iconImage.src = '/favicon-32x32.png';

            function setIcon(hrefs) {
                iconLinks.forEach((link, index) => {
                    link.href = Array.isArray(hrefs) ? hrefs[index] : hrefs;
                });
            }

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    frameIndex = 0;
                    isCursorVisible = true;
                    document.title = sleepFrames[frameIndex];
                    sleepTimer = setInterval(function () {
                        frameIndex = (frameIndex + 1) % sleepFrames.length;
                        document.title = sleepFrames[frameIndex];

                        if (iconOffHref) {
                            isCursorVisible = !isCursorVisible;
                            setIcon(isCursorVisible ? iconOnHref : iconOffHref);
                        }
                    }, 1000);
                } else {
                    clearInterval(sleepTimer);
                    document.title = originalTitle;
                    setIcon(originalIconHrefs);
                }
            });
        })();
    </script>

    @vite(['resources/js/app.js'])
    @livewireScriptConfig
</body>

</html>

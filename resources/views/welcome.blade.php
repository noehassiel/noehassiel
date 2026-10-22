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
        <a href="#contact" class="button button-small button-dark"><x-t key="nav.cta" /></a>
    </header>

    <main id="main">
        {{-- Hero --}}
        <section class="hero" id="top">
            <div class="container">
                <p class="eyebrow">
                    <span class="status-dot" aria-hidden="true"></span>
                    <x-t key="hero.available" />
                </p>

                <h1 class="hero-title">
                    <x-t key="hero.title_1" class="hero-title-line" />
                    <x-t key="hero.title_2" class="hero-title-line hero-title-accent" />
                </h1>

                <div class="hero-footer">
                    <p class="hero-lead"><x-t key="hero.lead" /></p>
                    <div class="hero-actions">
                        <a href="#contact" class="button button-primary"><x-t key="hero.primary" /></a>
                        <a href="#work" class="button button-ghost"><x-t key="hero.secondary" /></a>
                    </div>
                </div>

                <p class="hero-meta mono"><x-t key="hero.eyebrow" /></p>
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
            </div>
        </section>

        {{-- About --}}
        <section class="section" id="about" aria-labelledby="about-title">
            <div class="container about-grid">
                <div class="about-portrait">
                    @if (file_exists(public_path('images/hassiel.jpg')))
                        <img src="{{ asset('images/hassiel.jpg') }}" alt="{{ __('landing.about.portrait', locale: 'en') }}" width="960" height="1200" loading="lazy">
                    @else
                        <span class="about-portrait-empty mono" aria-hidden="true">n/h</span>
                    @endif
                </div>

                <div class="about-copy">
                    <p class="section-label mono">004 / <x-t key="about.label" /></p>
                    <h2 class="section-title" id="about-title"><x-t key="about.title" /></h2>
                    <p class="about-body"><x-t key="about.body_1" /></p>
                    <p class="about-body"><x-t key="about.body_2" /></p>

                    <dl class="about-facts">
                        @foreach (['based', 'speaks', 'stack'] as $fact)
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

    <footer class="site-footer">
        <div class="container footer-grid">
            <p class="footer-tagline"><x-t key="footer.tagline" /></p>
            <p class="footer-artisan mono" aria-hidden="true">$ php artisan make:something --with-intention</p>
            <p class="footer-meta mono">© {{ now()->year }} noehassiel · <x-t key="footer.rights" /></p>
        </div>
    </footer>

    @include('partials.dock')

    <script>
        document.addEventListener('visibilitychange', function () {
            document.title = document.hidden ? 'Zzzz...' : '{{ __('landing.meta.title', locale: 'en') }}';
        });
    </script>

    @vite(['resources/js/app.js'])
    @livewireScriptConfig
</body>

</html>

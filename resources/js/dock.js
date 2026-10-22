/*
 * Dock: live Puebla clock, Spotify now playing, active section highlighting, and showing the dock once the intro is done.
 */

const $dock = document.getElementById('dock');

if ($dock) {
    const $clock = $dock.querySelector('[data-clock]');
    const formatter = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'America/Mexico_City',
        hour: '2-digit',
        minute: '2-digit',
    });

    const tick = () => {
        $clock.textContent = formatter.format(new Date());
    };

    tick();
    setInterval(tick, 15000);

    const $music = document.getElementById('dock-music');

    if ($music) {
        const $cover = $music.querySelector('[data-music-cover]');
        const $title = $music.querySelector('[data-music-title]');
        const $artist = $music.querySelector('[data-music-artist]');
        const $progress = $music.querySelector('[data-music-progress]');
        let track = null;
        let syncedAt = 0;
        let pollTimer = null;
        let freshTimer = null;

        // Advance the bar locally between polls so it feels live
        const renderProgress = () => {
            if (!track) {
                return;
            }

            const elapsed = track.progress_ms + (Date.now() - syncedAt);
            $progress.style.transform = `scaleX(${Math.min(1, elapsed / track.duration_ms)})`;
        };

        const poll = async () => {
            clearTimeout(pollTimer);

            if (document.hidden) {
                return;
            }

            try {
                const response = await fetch($music.dataset.nowPlaying, { headers: { Accept: 'application/json' } });
                const data = await response.json();

                track = data.playing ? data.track : null;
                syncedAt = Date.now();
            } catch {
                track = null;
            }

            $music.hidden = !track;
            $dock.classList.toggle('has-music', Boolean(track));

            // Check again when the song should end, so the next one shows up right away
            const untilEnd = track ? track.duration_ms - track.progress_ms + 1500 : Infinity;
            pollTimer = setTimeout(poll, Math.min(15000, untilEnd));

            requestAnimationFrame(() => centerActiveLink($dock.querySelector('[aria-current="true"]')));

            if (!track) {
                return;
            }

            // Equalizer plays briefly whenever the song changes, then rests
            if ($music.href !== track.url) {
                $music.classList.add('is-fresh');
                clearTimeout(freshTimer);
                freshTimer = setTimeout(() => $music.classList.remove('is-fresh'), 4500);
            }

            $music.href = track.url;
            $music.setAttribute('aria-label', document.documentElement.dataset.lang === 'es'
                ? `Sonando ahora: ${track.title} de ${track.artist}. Abrir en Spotify (nueva pestaña)`
                : `Now playing: ${track.title} by ${track.artist}. Open in Spotify (new tab)`);
            $title.textContent = track.title;
            $artist.textContent = track.artist;

            $cover.hidden = !track.cover;

            if (track.cover && $cover.src !== track.cover) {
                $cover.src = track.cover;
            }

            renderProgress();
        };

        poll();
        setInterval(renderProgress, 1000);
        document.addEventListener('visibilitychange', poll);
    }

    const links = new Map(
        [...$dock.querySelectorAll('[data-dock-link]')].map((link) => [link.dataset.dockLink, link]),
    );

    // On narrow screens the numbers scroll horizontally; keep the active one centered
    const $apps = $dock.querySelector('.dock-apps');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const centerActiveLink = (link) => {
        if (!link || $apps.scrollWidth <= $apps.clientWidth) {
            return;
        }

        $apps.scrollTo({
            left: link.offsetLeft - ($apps.clientWidth - link.offsetWidth) / 2,
            behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            links.forEach((link, section) => {
                if (section === entry.target.id) {
                    link.setAttribute('aria-current', 'true');
                } else {
                    link.removeAttribute('aria-current');
                }
            });

            centerActiveLink(links.get(entry.target.id));
        });
    }, { rootMargin: '-45% 0px -50% 0px' });

    links.forEach((_, section) => {
        const target = document.getElementById(section);

        if (target) {
            observer.observe(target);
        }
    });
}

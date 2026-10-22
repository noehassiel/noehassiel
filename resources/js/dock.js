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

        // Advance the bar locally between polls so it feels live
        const renderProgress = () => {
            if (!track) {
                return;
            }

            const elapsed = track.progress_ms + (Date.now() - syncedAt);
            $progress.style.width = `${Math.min(100, (elapsed / track.duration_ms) * 100)}%`;
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

            // Check again when the song should end, so the next one shows up right away
            const untilEnd = track ? track.duration_ms - track.progress_ms + 1500 : Infinity;
            pollTimer = setTimeout(poll, Math.min(15000, untilEnd));

            if (!track) {
                return;
            }

            $music.href = track.url;
            $music.setAttribute('aria-label', `Now playing: ${track.title} by ${track.artist}. Open in Spotify`);
            $title.textContent = track.title;
            $artist.textContent = track.artist;

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
        });
    }, { rootMargin: '-45% 0px -50% 0px' });

    links.forEach((_, section) => {
        const target = document.getElementById(section);

        if (target) {
            observer.observe(target);
        }
    });
}

/*
 * Dock: live Puebla clock, active section highlighting, and showing the dock once the intro is done.
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

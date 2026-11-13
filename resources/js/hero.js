/*
 * Hero scroll: the portrait stays pinned and slowly pushes in while the headline lifts away,
 * then the work section slides up over the darkened photo. Desktop only, and never with reduced motion.
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const $hero = document.querySelector('.hero');

if ($hero) {
    // How long the hero stays pinned, as a share of the viewport height
    const pinLength = '+=140%';

    gsap.matchMedia().add('(min-width: 941px) and (prefers-reduced-motion: no-preference)', () => {
        // Pulls #work up a screen so it overlaps the pinned hero (see landing.css)
        document.documentElement.classList.add('hero-pinned');

        const scene = gsap.timeline({
            defaults: { ease: 'none' },
            scrollTrigger: {
                trigger: $hero,
                start: 'top top',
                end: pinLength,
                pin: true,
                scrub: 0.6,
                invalidateOnRefresh: true,
            },
        })
            .to('[data-hero-copy]', { yPercent: -16, opacity: 0, filter: 'blur(10px)', duration: 0.3 }, 0)
            .to('[data-hero-photo]', { scale: 1.3, duration: 1 }, 0)
            .to('[data-hero-shade]', { opacity: 0.7, duration: 0.6 }, 0.3);

        // The header is fixed while pinned (landing.css); slide it away just before the work title reaches it.
        // toggleClass is re-evaluated on every update, so fast jumps (anchor links, scrollTo) can't skip it.
        const headerTrigger = ScrollTrigger.create({
            start: () => scene.scrollTrigger.end - window.innerHeight * 0.3,
            end: 'max',
            toggleClass: { targets: '.site-header', className: 'is-hidden' },
        });

        return () => {
            headerTrigger.kill();
            document.documentElement.classList.remove('hero-pinned');
        };
    });

    // The intro locks scrolling; measure again once it hands the page over
    new MutationObserver((mutations, observer) => {
        if (!document.documentElement.classList.contains('os1-intro')) {
            observer.disconnect();
            ScrollTrigger.refresh();
        }
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
}

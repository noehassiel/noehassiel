/*
 * Brand mark: every so often the n/h isotipo backspaces itself, types a little message, then types the logo back.
 * Marks with data-brand-mark-compact only type short messages while that media query matches.
 */

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const messages = [
    // Greetings
    'hello', 'hey', 'hola', 'hi :)', 'qué tal', 'buenas', 'yo',
    // Status
    'sleeping', 'zzz', 'dev mode', 'brb', 'afk', 'coffee', 'café', 'tacos', 'thinking', 'focus',
    // Craft
    'shipping', 'deploying', 'compiling', 'pixels', 'by hand', 'crafting', 'git push', 'npm run dev',
    // Laravel easter eggs
    'php artisan', 'artisan', 'dd()', '200 ok', '404?', 'sudo',
    // Little winks
    'scroll on', 'nice cursor', 'made in mx', 'puebla', ':)', '<3',
];
const compactMaxLength = 5;

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
const between = (min, max) => min + Math.random() * (max - min);

const animate = ($mark) => {
    const $text = $mark.querySelector('[data-brand-text]');
    const logoHtml = $text.innerHTML;
    const compactQuery = $mark.dataset.brandMarkCompact ? window.matchMedia($mark.dataset.brandMarkCompact) : null;
    let lastMessage = null;

    // Removes the last character, keeping the ember slash span intact until its turn
    const backspace = async () => {
        while ($text.textContent.length) {
            const $last = $text.lastChild;

            if ($last.nodeType === Node.TEXT_NODE && $last.textContent.length > 1) {
                $last.textContent = $last.textContent.slice(0, -1);
            } else {
                $last.remove();
            }

            await wait(between(50, 90));
        }
    };

    const type = async (chars) => {
        for (const char of chars) {
            $text.append(char);
            await wait(between(70, 140));
        }
    };

    const typeLogo = async () => {
        $text.append('n');
        await wait(between(90, 140));

        const $slash = document.createElement('span');
        $slash.className = 'brand-slash';
        $slash.textContent = '/';
        $text.append($slash);
        await wait(between(90, 140));

        $text.append('h');
    };

    const pickMessage = () => {
        const isCompact = compactQuery?.matches ?? false;
        const options = messages.filter((message) => message !== lastMessage && (!isCompact || message.length <= compactMaxLength));
        lastMessage = options[Math.floor(Math.random() * options.length)];

        return lastMessage;
    };

    const play = async () => {
        $mark.classList.add('is-typing');
        await backspace();
        await wait(300);

        // Sometimes a single message, sometimes a little chain of two or three before the logo returns
        const count = 1 + Math.floor(Math.random() * 3);

        for (let index = 0; index < count; index++) {
            $mark.classList.add('is-typing');
            await type(pickMessage());

            $mark.classList.remove('is-typing');
            await wait(index === count - 1 ? between(2000, 3000) : between(1200, 1800));

            $mark.classList.add('is-typing');
            await backspace();
            await wait(250);
        }

        await typeLogo();

        $text.innerHTML = logoHtml;
        $mark.classList.remove('is-typing');
    };

    const loop = async () => {
        await wait(between(4000, 6000));

        // Skip the bit while the tab is hidden so it never lands mid-word on return
        if (!document.hidden) {
            await play();
        }

        loop();
    };

    loop();
};

if (!prefersReducedMotion) {
    document.querySelectorAll('[data-brand-mark]').forEach(animate);
}

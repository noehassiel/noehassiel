/*
 * Footer horizon: the dark contact section becomes a planet seen from orbit, and the footer is its sunrise.
 *
 * The glow is worked out per pixel at a quarter of the canvas size and upscaled, so it stays soft and cheap.
 * Its colour runs out from the planet's edge through white, gold, orange and red, reaches furthest and starts
 * hottest in the middle where the sun sits, and toward the sides it thins and starts already orange-red.
 * Past the warm colours it fades into the stone of the page. The planet is then filled crisp at full
 * resolution with a hairline rim (white at the sun, cool toward the sides) and a faint haze inside its edge.
 *
 * Scrolling doesn't slide the edge up the screen, it flattens the curve, like coming down toward the planet:
 * the planet hangs from the top, the corners light first, and it straightens into a flat sunrise glow.
 * The footer copy waits for that to settle and then rises in on its own, so it's never caught halfway.
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { CustomEase } from 'gsap/CustomEase';

gsap.registerPlugin(ScrollTrigger, CustomEase);

const options = {
    // How round the horizon stays once it settles: the dip of its middle, as a share of the width
    roundness: 0.03,
    // Where the settled horizon sits, as a share of the viewport height
    horizon: 0.1,
    // How deep the planet's middle hangs when the footer starts to come in, as a share of the viewport height
    startDepth: 0.55,
    // How far the glow reaches below the middle of the horizon, as a share of the viewport height
    glowReach: 0.46,
    // How much of that reach is left at the sides
    sideReach: 0.32,
    // How far along the ramp the sides already start (0 is white, 1 has faded out)
    sideHeat: 0.38,
    // Scroll distance for the whole flattening: ScrollTrigger's end, measured from the footer's top
    scrollEnd: 'top top',
    // The glow from the edge outward. Swap it for a night blue, a green, or anything that suits the page.
    ramp: [
        [0, '#FFFDF7'],
        [0.1, '#FFEFD2'],
        [0.24, '#FFC57E'],
        [0.42, '#FF8F4D'],
        [0.6, '#FF5A26'],
        [0.76, '#D9452A'],
        [1, '#C8584A'],
    ],
    // Where the ramp starts fading into the page, and where it is gone
    fade: [0.62, 1],
    planet: '#0A0A0A',
    rim: ['#FFFFFF', '#7FA9D6'],
    haze: '#3D6FA8',
};

// One quarter of the canvas size, per axis
const GLOW_SCALE = 4;

const $footer = document.querySelector('[data-footer-horizon]');

if ($footer) {
    const canvas = document.createElement('canvas');
    canvas.className = 'footer-horizon';
    canvas.setAttribute('aria-hidden', 'true');
    $footer.prepend(canvas);
    $footer.classList.add('has-horizon');

    const context = canvas.getContext('2d');
    const glowCanvas = document.createElement('canvas');
    const glowContext = glowCanvas.getContext('2d');
    const ramp = buildRamp(options.ramp);
    // Slow to leave the round shape, then settles gently into the flat horizon
    const flatten = CustomEase.create('horizonFlatten', 'M0,0 C0.18,0.02 0.32,0.42 0.52,0.7 0.7,0.94 0.82,1 1,1');

    let width = 0;
    let height = 0;
    let progress = 1;
    let frame = 0;

    const render = () => {
        frame = 0;

        if (!width || !height) {
            return;
        }

        const shape = horizonShape(flatten(progress));
        paintGlow(shape);

        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        context.clearRect(0, 0, width, height);
        context.imageSmoothingEnabled = true;
        context.imageSmoothingQuality = 'high';
        context.drawImage(glowCanvas, 0, 0, width, height);
        paintPlanet(shape);
    };

    const requestRender = () => {
        frame ||= requestAnimationFrame(render);
    };

    const resize = () => {
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        width = canvas.clientWidth;
        height = canvas.clientHeight;
        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        glowCanvas.width = Math.ceil(width / GLOW_SCALE);
        glowCanvas.height = Math.ceil(height / GLOW_SCALE);
        render();
    };

    new ResizeObserver(resize).observe(canvas);

    /**
     * The planet's lower edge is an arc through the middle of the horizon. As the curve flattens its middle
     * rises toward the settled horizon and its sides drop toward it.
     */
    function horizonShape(amount) {
        const halfWidth = width / 2;
        const settledSag = Math.max(options.roundness * width, 0.5);
        const centerY = gsap.utils.interpolate(options.startDepth * height, options.horizon * height, amount);
        // Starts with the sides at the top corners; a circle can't dip further than about its half width
        const startSag = Math.min(options.startDepth * height, halfWidth * 0.9);
        const sag = gsap.utils.interpolate(startSag, settledSag, amount);
        const radius = (halfWidth * halfWidth + sag * sag) / (2 * sag);

        return {
            halfWidth,
            centerY,
            radius,
            circleY: centerY - radius,
            // The glow grows a little as the horizon opens up
            reach: options.glowReach * height * gsap.utils.interpolate(0.7, 1, amount),
        };
    }

    function edgeY(shape, x) {
        const offset = x - shape.halfWidth;

        return shape.circleY + Math.sqrt(Math.max(shape.radius * shape.radius - offset * offset, 0));
    }

    function paintGlow(shape) {
        const glowWidth = glowCanvas.width;
        const glowHeight = glowCanvas.height;
        const image = glowContext.createImageData(glowWidth, glowHeight);
        const pixels = image.data;
        const [fadeStart, fadeEnd] = options.fade;

        for (let column = 0; column < glowWidth; column++) {
            const x = (column + 0.5) * GLOW_SCALE;
            const edge = edgeY(shape, x);
            const offset = x - shape.halfWidth;
            const side = Math.pow(Math.min(Math.abs(offset) / shape.halfWidth, 1.2), 1.3);
            const reach = shape.reach * gsap.utils.interpolate(1, options.sideReach, side);
            const heat = options.sideHeat * side;

            for (let row = 0; row < glowHeight; row++) {
                const y = (row + 0.5) * GLOW_SCALE;
                // Inside the planet keeps the hottest colour, so upscaling never smears a dark fringe past the edge
                const distance = y > edge ? Math.hypot(offset, y - shape.circleY) - shape.radius : 0;
                const position = Math.min(distance / reach + heat, 1);
                const alpha = 1 - smoothstep(fadeStart, fadeEnd, position);
                const color = ramp[Math.round(position * (ramp.length - 1))];
                const index = (row * glowWidth + column) * 4;

                pixels[index] = color[0];
                pixels[index + 1] = color[1];
                pixels[index + 2] = color[2];
                pixels[index + 3] = alpha * 255;
            }
        }

        glowContext.putImageData(image, 0, 0);
    }

    function paintPlanet(shape) {
        const edge = new Path2D();
        const planet = new Path2D();
        planet.moveTo(-2, -2);

        for (let x = -2; x <= width + 4; x += 2) {
            const y = edgeY(shape, x);
            edge.lineTo(x, y);
            planet.lineTo(x, y);
        }

        planet.lineTo(width + 4, -2);
        planet.closePath();

        context.fillStyle = options.planet;
        context.fill(planet);

        // A faint haze just inside the edge: a soft shadow of the rim, clipped to the planet
        context.save();
        context.clip(planet);
        context.strokeStyle = options.haze;
        context.shadowColor = options.haze;
        context.shadowBlur = 36;
        context.globalAlpha = 0.45;
        context.lineWidth = 2;
        context.stroke(edge);
        context.restore();

        // The hairline rim: white where the sun sits, cool toward the sides
        const rim = context.createLinearGradient(0, 0, width, 0);
        rim.addColorStop(0, options.rim[1]);
        rim.addColorStop(0.5, options.rim[0]);
        rim.addColorStop(1, options.rim[1]);
        context.strokeStyle = rim;
        context.globalAlpha = 0.35;
        context.lineWidth = 4;
        context.stroke(edge);
        context.globalAlpha = 1;
        context.lineWidth = 1.25;
        context.stroke(edge);
    }

    const $reveal = $footer.querySelectorAll('[data-horizon-reveal]');

    gsap.matchMedia().add({
        motion: '(prefers-reduced-motion: no-preference)',
        reduced: '(prefers-reduced-motion: reduce)',
    }, ({ conditions }) => {
        if (conditions.reduced) {
            progress = 1;
            requestRender();

            return;
        }

        gsap.set($reveal, { autoAlpha: 0, y: 40 });

        const flattenTrigger = ScrollTrigger.create({
            trigger: $footer,
            start: 'top bottom',
            end: options.scrollEnd,
            invalidateOnRefresh: true,
            onUpdate: (self) => {
                progress = self.progress;
                requestRender();
            },
            onRefresh: (self) => {
                progress = self.progress;
                requestRender();
            },
        });

        // The copy rises in once the horizon has settled, and stays. Starts a hair early so landing exactly on the footer counts.
        const revealTrigger = ScrollTrigger.create({
            trigger: $footer,
            start: 'top 4%',
            once: true,
            onEnter: () => {
                gsap.to($reveal, { autoAlpha: 1, y: 0, duration: 1.1, ease: 'expo.out', stagger: 0.12 });
            },
        });

        return () => {
            flattenTrigger.kill();
            revealTrigger.kill();
        };
    });
}

/**
 * Precomputes 256 colours along the ramp so the per-pixel loop only does a lookup.
 *
 * @param {Array<[number, string]>} stops
 * @returns {Array<[number, number, number]>}
 */
function buildRamp(stops) {
    const colors = stops.map(([position, hex]) => [position, hexToRgb(hex)]);

    return Array.from({ length: 256 }, (_, step) => {
        const position = step / 255;
        const nextIndex = colors.findIndex(([stop]) => stop >= position);
        const [toStop, to] = colors[Math.max(nextIndex, 0)];
        const [fromStop, from] = colors[Math.max(nextIndex - 1, 0)];
        const mix = toStop === fromStop ? 0 : (position - fromStop) / (toStop - fromStop);

        return from.map((channel, index) => Math.round(channel + (to[index] - channel) * mix));
    });
}

function hexToRgb(hex) {
    const value = parseInt(hex.slice(1), 16);

    return [(value >> 16) & 255, (value >> 8) & 255, value & 255];
}

function smoothstep(from, to, value) {
    const amount = Math.min(Math.max((value - from) / (to - from), 0), 1);

    return amount * amount * (3 - 2 * amount);
}

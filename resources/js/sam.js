import * as THREE from 'three';
import { gsap } from 'gsap';

/*
 * OS1 launch sequence (Her).
 * A single GSAP timeline is the clock; the Three.js knot only reads from it.
 * INIT_BOOT -> ACTIVE_WAVEFORM -> COLLAPSE_BEAD -> PANEL_DISSOLVE -> DESKTOP_CASCADE
 */

const $root = document.getElementById('sam');
const $wrap = document.getElementById('wrap');

if ($root && $wrap) {
    const OS_RED = 0xd93829;

    const timing = {
        boot: 0.8,
        collapse: 4,
        // How long the finished ring holds before the audio ends
        ringHold: 0.6,
        dissolve: 0.6,
    };

    // Used when audio metadata is unavailable (start.ogg is ~14.2s)
    const fallbackAudioDuration = 14.2;

    const knotLength = 60;
    const knotRadius = 7.4;
    const baseSpin = 0.035;
    // Knot spans ~24% of the panel width, as in the film
    const knotScreenRatio = 0.24;
    // Film ring: ~9.6% of panel width, stroke ~8% of its diameter
    const ringScale = 2.33;
    const ringOuter = 5.55;
    const ringInner = ringOuter * 0.84;
    const pi2 = Math.PI * 2;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Shared state the render loop reads; tweened by the timeline.
    const collapse = { progress: 0 };

    let rafId = null;
    let renderer, scene, camera, group, mesh, ring, ringcover;

    const setState = (state) => {
        $root.dataset.state = state;
        $root.dispatchEvent(new CustomEvent('os1:state', { detail: { state }, bubbles: true }));
    };

    class KnotCurve extends THREE.Curve {
        getPoint(percent) {
            const x = knotLength * Math.sin(pi2 * percent);
            const y = knotRadius * Math.cos(pi2 * 3 * percent);

            let t = (percent % 0.25) / 0.25;
            t = (percent % 0.25) - (2 * (1 - t) * t * -0.0185 + t * t * 0.25);
            if (Math.floor(percent / 0.25) === 0 || Math.floor(percent / 0.25) === 2) {
                t *= -1;
            }
            const z = knotRadius * Math.sin(pi2 * 2 * (percent - t));

            return new THREE.Vector3(x, y, z);
        }
    }

    const buildScene = () => {
        camera = new THREE.PerspectiveCamera(65, 1, 1, 10000);
        scene = new THREE.Scene();
        group = new THREE.Group();
        scene.add(group);

        mesh = new THREE.Mesh(
            new THREE.TubeGeometry(new KnotCurve(), 200, 1.6, 2, true),
            // Must stay opaque so it renders before the shadow planes; see render()
            new THREE.MeshBasicMaterial({ color: 0xfbeee0 })
        );
        group.add(mesh);

        // Panel that hides the knot's tip once it faces the camera
        ringcover = new THREE.Mesh(
            new THREE.PlaneGeometry(50, ringOuter * 2 * ringScale + 4, 1),
            new THREE.MeshBasicMaterial({ color: OS_RED, opacity: 0, transparent: true })
        );
        ringcover.position.x = knotLength + 1;
        ringcover.rotation.y = Math.PI / 2;
        group.add(ringcover);

        // The "voice bead" the waveform collapses into
        ring = new THREE.Mesh(
            new THREE.RingGeometry(ringInner, ringOuter, 96),
            new THREE.MeshBasicMaterial({ color: 0xffffff, opacity: 0, transparent: true })
        );
        ring.position.x = knotLength + 1.1;
        ring.rotation.y = Math.PI / 2;
        group.add(ring);

        // Fake depth shadow
        for (let i = 0; i < 10; i++) {
            const plane = new THREE.Mesh(
                new THREE.PlaneGeometry(knotLength * 2 + 1, knotRadius * 3, 1),
                new THREE.MeshBasicMaterial({ color: OS_RED, transparent: true, opacity: 0.13 })
            );
            plane.position.z = -2.5 + i * 0.5;
            group.add(plane);
        }

        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setClearColor(OS_RED);
        $wrap.appendChild(renderer.domElement);

        resize();
        window.addEventListener('resize', resize);
    };

    // Fit the canvas to the frame and pull the camera back so the knot never clips on narrow screens.
    function resize() {
        const width = $wrap.clientWidth;
        const height = $wrap.clientHeight;
        const aspect = width / height;
        const halfFov = THREE.MathUtils.degToRad(camera.fov / 2);

        camera.aspect = aspect;
        camera.position.z = Math.max(
            (knotLength * 2) / (knotScreenRatio * 2 * Math.tan(halfFov) * aspect),
            // Keep the knot from overflowing tall (portrait) panels
            (knotLength + 15) / (Math.tan(halfFov) * aspect)
        );
        camera.updateProjectionMatrix();
        renderer.setSize(width, height, false);
    }

    const render = () => {
        const acceleration = collapse.progress;

        if (acceleration > 0.35) {
            const turn = (acceleration - 0.35) / 0.65;
            group.rotation.y = (-Math.PI / 2) * turn;
            group.position.z = 50 * turn;

            const bead = Math.max(0, (acceleration - 0.97) / 0.03);
            if (bead > 0 && !mesh.material.transparent) {
                mesh.material.transparent = true;
                mesh.material.needsUpdate = true;
            }
            mesh.material.opacity = 1 - bead;
            ringcover.material.opacity = ring.material.opacity = bead;
            ring.scale.x = ring.scale.y = ringScale * (0.9 + 0.1 * bead);
        }

        renderer.render(scene, camera);
    };

    let lastTime = performance.now();

    const animate = (now) => {
        // Normalise to 60fps so spin speed is identical on 120Hz displays
        const frames = Math.min((now - lastTime) / (1000 / 60), 4);
        lastTime = now;

        mesh.rotation.x += (baseSpin + collapse.progress) * frames;
        render();
        rafId = requestAnimationFrame(animate);
    };

    const teardown = () => {
        if (rafId !== null) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }

        window.removeEventListener('resize', resize);

        if (renderer) {
            scene.traverse((object) => {
                object.geometry?.dispose();
                object.material?.dispose();
            });
            renderer.dispose();
            renderer.domElement.remove();
        }
    };

    const windows = gsap.utils.toArray('.os1-window');
    const dockIcons = gsap.utils.toArray('.os1-dock-icon');

    // Diagonal 3D fan: each window steps right, up and towards the viewer
    const fan = (k) => ({ x: k * 70, y: k * -46, z: k * 60, rotationY: -28, rotationX: 8 });

    gsap.set('#os1-frame', { autoAlpha: 0, scale: 0.98 });
    gsap.set('#os1-progress-bar', { scaleX: 0, transformOrigin: '0% 50%' });
    gsap.set(dockIcons, { autoAlpha: 0, scale: 0.6 });
    gsap.set(windows, { ...fan(0), autoAlpha: 0, z: -200 });

    /*
     * Timeline length is derived from the audio so the ring completes `ringHold`
     * seconds before the sound ends, and the panel dissolves right as it ends.
     */
    const buildTimeline = (audioDuration) => {
        const waveform = Math.max(2, audioDuration - timing.boot - timing.collapse - timing.ringHold);

        const timeline = gsap.timeline({
            paused: true,
            defaults: { ease: 'power3.out' },
            onComplete: () => $root.dispatchEvent(new CustomEvent('os1:complete', { bubbles: true })),
        });

        timeline
            .addLabel('boot')
            .call(setState, ['INIT_BOOT'])
            .to('#os1-frame', { autoAlpha: 1, scale: 1, duration: timing.boot })

            .addLabel('waveform')
            .call(setState, ['ACTIVE_WAVEFORM'])
            .to('#os1-progress-bar', { scaleX: 1, duration: waveform, ease: 'none' })

            .addLabel('collapse')
            .call(setState, ['COLLAPSE_BEAD'])
            .to('#os1-progress', { autoAlpha: 0, duration: 0.4 }, 'collapse')
            .to(collapse, { progress: 1, duration: timing.collapse, ease: 'power3.inOut' }, 'collapse')
            .to({}, { duration: timing.ringHold })

            .addLabel('dissolve')
            .call(setState, ['PANEL_DISSOLVE'])
            .to('#os1-frame', { autoAlpha: 0, scale: 0.95, duration: timing.dissolve, ease: 'power2.out' })
            .call(teardown)

            .addLabel('cascade')
            .call(setState, ['DESKTOP_CASCADE'])
            .to(dockIcons, { autoAlpha: 1, scale: 1, duration: 0.5, stagger: 0.05 }, 'cascade')
            .to(windows, {
                autoAlpha: 1,
                x: (k) => fan(k).x,
                y: (k) => fan(k).y,
                z: (k) => fan(k).z,
                duration: 0.9,
                stagger: 0.08,
                ease: 'expo.out',
            }, 'cascade+=0.15');

        return timeline;
    };

    // Resolve the real audio duration, falling back if metadata never arrives
    const audioDuration = (audio) => new Promise((resolve) => {
        if (!audio) {
            return resolve(fallbackAudioDuration);
        }

        if (audio.readyState >= 1 && Number.isFinite(audio.duration)) {
            return resolve(audio.duration);
        }

        const timeout = setTimeout(() => resolve(fallbackAudioDuration), 1500);
        audio.addEventListener('loadedmetadata', () => {
            clearTimeout(timeout);
            resolve(Number.isFinite(audio.duration) ? audio.duration : fallbackAudioDuration);
        }, { once: true });
    });

    /*
     * While the audio plays it is the clock: the timeline is scrubbed to
     * audio.currentTime every tick, so drift or buffering can't desync them.
     * If autoplay is blocked the timeline runs on its own, and the audio joins
     * at the matching offset on the first user gesture.
     */
    const syncToAudio = (timeline, audio) => {
        const audioEnd = timeline.labels.dissolve;

        const follow = () => {
            if (!audio.paused && !audio.ended) {
                timeline.time(Math.min(audio.currentTime, audioEnd));
            }
        };

        const startAudio = () => audio.play().then(() => {
            timeline.pause();
            gsap.ticker.add(follow);
        });

        audio.addEventListener('ended', () => {
            gsap.ticker.remove(follow);
            timeline.play(audioEnd);
        }, { once: true });

        startAudio().catch(() => {
            timeline.play();

            const resume = () => {
                if (timeline.time() >= audioEnd) {
                    return;
                }
                audio.currentTime = timeline.time();
                startAudio().catch(() => {});
            };

            ['pointerdown', 'keydown', 'touchstart'].forEach((type) => {
                document.addEventListener(type, resume, { once: true });
            });
        });
    };

    const audio = document.getElementById('myAudio');

    window.os1 = { teardown };

    audioDuration(audio).then((duration) => {
        const timeline = buildTimeline(duration);
        window.os1.timeline = timeline;

        if (prefersReducedMotion) {
            timeline.progress(1);

            return;
        }

        buildScene();
        rafId = requestAnimationFrame(animate);

        if (audio) {
            syncToAudio(timeline, audio);
        } else {
            timeline.play();
        }
    });
}

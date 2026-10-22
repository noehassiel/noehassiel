/*
 * OS1 conversation: greeting -> listen -> reply -> speak, looped until the
 * visitor leaves or the server ends it. Voice states are mirrored on
 * #sam[data-voice] (idle | listening | thinking | speaking) for styling.
 */

import { getLanguage } from './language.js';

const $root = document.getElementById('sam');
const $talk = document.getElementById('os1-talk');

if ($root && $talk) {
    const $caption = document.getElementById('os1-caption');
    const $leave = document.getElementById('os1-leave');
    const $textForm = document.getElementById('os1-text-form');
    const $voice = document.getElementById('os1-voice');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    const copy = {
        en: {
            start: 'Talk to me',
            idle: 'Tap to talk',
            listening: 'Listening…',
            thinking: 'Thinking…',
            speaking: 'Speaking…',
            leave: 'Enter the site →',
            placeholder: 'Write to me…',
            inputLabel: 'Your message',
            send: 'Send',
            tooFast: "We're going too fast. Give me a moment and let's try again.",
            failed: 'Something broke on my side. Shall we try again?',
            offline: 'I lost the connection for a second. Shall we try again?',
            micUnavailable: "I can't hear you from this browser. Write to me instead.",
            recognition: 'en-US',
        },
        es: {
            start: 'Háblame',
            idle: 'Toca para hablar',
            listening: 'Te escucho…',
            thinking: 'Pensando…',
            speaking: 'Hablando…',
            leave: 'Entrar al sitio →',
            placeholder: 'Escríbeme…',
            inputLabel: 'Tu mensaje',
            send: 'Enviar',
            tooFast: 'Vamos muy rápido. Dame un momento y lo intentamos otra vez.',
            failed: 'Algo falló de mi lado. ¿Lo intentamos de nuevo?',
            offline: 'Perdí la conexión un segundo. ¿Lo intentamos de nuevo?',
            micUnavailable: 'No te escucho desde este navegador. Mejor escríbeme.',
            recognition: 'es-MX',
        },
    };

    const greetings = JSON.parse($root.dataset.greetings);
    const voiceUrls = JSON.parse($root.dataset.voiceUrls);
    const $toggle = document.getElementById('os1-toggle');
    const $minimize = document.getElementById('os1-minimize');

    // English by default; shared with the landing through language.js
    let language = getLanguage();

    const t = (key) => copy[language][key];

    let mode = 'idle';
    // New conversation per page visit; the server caps turns per conversation
    const conversation = crypto.randomUUID();

    let started = false;
    let recognition = null;

    const setMode = (next) => {
        mode = next;
        $root.dataset.voice = mode;
        $talk.textContent = started ? t(mode) : t('start');
        $talk.disabled = mode === 'thinking' || mode === 'speaking';
    };

    const isDocked = () => $root.dataset.state === 'DOCKED';

    // Samantha only listens while her panel is visible: the intro, or the docked ring opened
    const canListen = () => !isDocked() || $root.dataset.open === 'true';

    const setOpen = (open) => {
        $root.dataset.open = String(open);
        $toggle.setAttribute('aria-expanded', String(open));

        if (!open) {
            recognition?.abort();
        }
    };

    const renderLanguage = () => {
        $leave.textContent = t('leave');
        $textForm.querySelector('input').placeholder = t('placeholder');
        $textForm.querySelector('input').setAttribute('aria-label', t('inputLabel'));
        $textForm.querySelector('button').textContent = t('send');
        setMode(mode);
    };

    const showCaption = (text) => {
        $caption.textContent = text;
    };

    const speak = (src, text) => new Promise((resolve) => {
        showCaption(text);
        setMode('speaking');

        $voice.src = src;
        window.os1?.setVoice($voice);

        // Resolves true only when the voice played to the end, so autoplay failures don't open the mic
        const done = (playedToEnd) => {
            setMode('idle');
            resolve(playedToEnd);
        };

        $voice.addEventListener('ended', () => done(true), { once: true });
        $voice.play().catch(() => done(false));
    });

    // Hand the turn back to the visitor as soon as OS1 stops talking
    const speakThenListen = async (src, text) => {
        const playedToEnd = await speak(src, text);

        if (playedToEnd && canListen()) {
            listen();
        }
    };

    // Leave the intro and land on the section Samantha picked once the landing is revealed
    const goToSection = (section) => {
        const scroll = () => document.getElementById(section)?.scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
        });

        if (isDocked()) {
            setOpen(false);
            scroll();

            return;
        }

        $root.addEventListener('os1:complete', scroll, { once: true });
        window.os1?.leave();
    };

    const send = async (message) => {
        setMode('thinking');

        try {
            const response = await fetch($root.dataset.replyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ message, language, conversation }),
            });

            if (response.status === 429) {
                showCaption(t('tooFast'));
                setMode('idle');

                return;
            }

            const data = await response.json();

            if (!response.ok) {
                showCaption(data.errors?.message?.[0] ?? t('failed'));
                setMode('idle');

                return;
            }

            if (data.finished) {
                await speak(data.audio_url, data.text);
                setTimeout(() => (isDocked() ? setOpen(false) : window.os1?.leave()), 800);

                return;
            }

            if (data.section) {
                await speak(data.audio_url, data.text);
                goToSection(data.section);

                return;
            }

            await speakThenListen(data.audio_url, data.text);
        } catch {
            showCaption(t('offline'));
            setMode('idle');
        }
    };

    // Set once recognition fails for good (e.g. Arc/Brave: Chromium without Google's speech service)
    let micUnavailable = false;

    const listen = () => {
        if (!Recognition || micUnavailable) {
            $textForm.hidden = false;
            $textForm.querySelector('input').focus();

            return;
        }

        recognition = new Recognition();
        recognition.lang = t('recognition');
        recognition.interimResults = true;
        recognition.continuous = false;

        let transcript = '';

        // Chrome waits 1–2s of silence before ending; stop sooner once words stop arriving
        let silenceTimer = null;

        recognition.onresult = (event) => {
            transcript = Array.from(event.results).map((result) => result[0].transcript).join(' ');
            showCaption(transcript);

            clearTimeout(silenceTimer);
            silenceTimer = setTimeout(() => recognition?.stop(), 800);
        };

        recognition.onerror = (event) => {
            console.warn('OS1 speech recognition error:', event.error);

            // "no-speech" and "aborted" are normal; anything else means recognition can't work here
            if (event.error !== 'no-speech' && event.error !== 'aborted') {
                micUnavailable = true;
                showCaption(t('micUnavailable'));
                $textForm.hidden = false;
                $textForm.querySelector('input').focus();
            }
        };

        recognition.onend = () => {
            recognition = null;

            if (transcript.trim()) {
                send(transcript.trim());
            } else {
                setMode('idle');
            }
        };

        setMode('listening');
        recognition.start();
    };

    const greet = async () => {
        if (started) {
            return;
        }

        started = true;
        const playedToEnd = await speak(voiceUrls[language].greeting, greetings[language]);

        if (!playedToEnd) {
            // Autoplay blocked: fall back to the "Talk to me" button, whose click unlocks audio
            started = false;
            showCaption('');
            setMode('idle');

            return;
        }

        if (canListen()) {
            listen();
        }
    };

    // Greet the visitor as soon as the ring is ready, without waiting for a click
    $root.addEventListener('os1:state', (event) => {
        if (event.detail.state === 'CONVERSATION') {
            greet();
        }
    });

    $talk.addEventListener('click', () => {
        if (!started) {
            greet();

            return;
        }

        if (recognition) {
            recognition.stop();

            return;
        }

        listen();
    });

    $textForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const input = $textForm.querySelector('input');
        const message = input.value.trim();

        if (message) {
            input.value = '';
            send(message);
        }
    });

    $leave.addEventListener('click', () => {
        recognition?.abort();
        $voice.pause();
        window.os1?.leave();
    });

    $toggle.addEventListener('click', () => {
        setOpen(true);

        if (!started) {
            greet();

            return;
        }

        // Reopening: start clean and wait for the visitor to tap, instead of replaying her last answer
        if (mode === 'idle') {
            showCaption('');
            setMode('idle');
        }
    });

    $minimize.addEventListener('click', () => setOpen(false));

    $root.addEventListener('os1:state', (event) => {
        if (event.detail.state === 'DOCKED') {
            setOpen(false);
        }
    });

    window.addEventListener('language:change', (event) => {
        language = event.detail.language;
        recognition?.abort();
        renderLanguage();
    });

    renderLanguage();
}

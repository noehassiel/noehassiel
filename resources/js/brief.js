/*
 * Project brief with Samantha: she reads each question aloud (cached TTS), and open answers
 * can be dictated with the browser's speech recognition. Everything stays editable as text.
 */

import { getLanguage } from './language.js';

const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const voice = new Audio();

let muted = false;
try {
    muted = localStorage.getItem('brief.muted') === 'true';
} catch {
    // Storage blocked: default to speaking
}

let recognition = null;
let micUnavailable = !Recognition;

const syncState = () => {
    document.documentElement.classList.toggle('brief-muted', muted);
    document.documentElement.classList.toggle('brief-no-mic', micUnavailable);
    document.querySelectorAll('[data-brief-mute]').forEach((button) => {
        button.setAttribute('aria-pressed', String(muted));
    });
};

window.brief = {
    ask(key) {
        syncState();
        recognition?.abort();

        const $brief = document.querySelector('[data-brief]');

        if (muted || !$brief) {
            return;
        }

        voice.src = $brief.dataset.lineUrl.replace('__KEY__', key) + `?language=${getLanguage()}`;
        window.os1?.setVoice(voice);
        voice.play().catch(() => {});
    },
};

const dictate = (button) => {
    const input = document.getElementById(button.dataset.briefSpeak);

    if (recognition) {
        recognition.stop();

        return;
    }

    voice.pause();

    const before = button.hasAttribute('data-brief-append') && input.value.trim() ? `${input.value.trim()} ` : '';

    recognition = new Recognition();
    recognition.lang = getLanguage() === 'es' ? 'es-MX' : 'en-US';
    recognition.interimResults = true;

    recognition.onresult = (event) => {
        const heard = Array.from(event.results).map((result) => result[0].transcript).join(' ').trim();
        input.value = before + heard;
        // Let wire:model pick up the dictated text
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    recognition.onerror = (event) => {
        // Arc/Brave/Firefox: no speech service; typing still works
        if (event.error !== 'no-speech' && event.error !== 'aborted') {
            micUnavailable = true;
            syncState();
            input.focus();
        }
    };

    recognition.onend = () => {
        recognition = null;
        button.removeAttribute('data-listening');
    };

    button.setAttribute('data-listening', '');
    recognition.start();
};

document.addEventListener('click', (event) => {
    const speak = event.target.closest('[data-brief-speak]');

    if (speak && Recognition) {
        dictate(speak);

        return;
    }

    if (event.target.closest('[data-brief-mute]')) {
        muted = !muted;

        try {
            localStorage.setItem('brief.muted', String(muted));
        } catch {
            // Storage blocked: the choice just won't persist
        }

        if (muted) {
            voice.pause();
        }

        syncState();
    }
});

syncState();

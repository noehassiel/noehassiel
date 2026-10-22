/*
 * Site language (en | es), shared by Samantha and the landing.
 * Every [data-set-language] button switches it; html[data-lang] drives which copy is visible.
 */

const STORAGE_KEY = 'os1.language';

export function getLanguage() {
    try {
        return localStorage.getItem(STORAGE_KEY) === 'es' ? 'es' : 'en';
    } catch {
        return 'en';
    }
}

export function setLanguage(language) {
    try {
        localStorage.setItem(STORAGE_KEY, language);
    } catch {
        // Storage blocked: the choice just won't persist
    }

    document.documentElement.dataset.lang = language;
    document.documentElement.lang = language;

    document.querySelectorAll('[data-set-language]').forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.setLanguage === language));
    });

    window.dispatchEvent(new CustomEvent('language:change', { detail: { language } }));
    window.Livewire?.dispatch('language-changed', { language });
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-set-language]');

    if (button) {
        setLanguage(button.dataset.setLanguage);
    }
});

document.addEventListener('DOMContentLoaded', () => setLanguage(getLanguage()));

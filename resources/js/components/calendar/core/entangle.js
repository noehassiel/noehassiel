/**
 * Create a Livewire entanglement for pure Livewire binding
 * @param {Object} livewire - Livewire instance
 * @param {string} prop - Property name to entangle
 * @param {boolean} live - Whether to use live entanglement
 * @returns {Object} Entangled binding
 */
export const $entangle = (livewire, prop, live) => {
    const binding = livewire.$entangle(prop);
    return live ? binding.live : binding;
};

/**
 * Initialize state with optional Livewire entanglement for hybrid setup
 * @param {Object|null} livewire - Livewire instance or null
 * @param {string} model - Model property name
 * @param {boolean} isLive - Whether to use live entanglement
 * @returns {Object|null} Entangled binding or null if no Livewire instance
 */
export const $initState = (livewire, model, isLive) => {

    if (!livewire || !model) return null;

    return $entangle(livewire, model, isLive);
};
import PRESET_HANDLERS from './presets';

export default class PresetsManager {


    constructor(activated = []) {
        activated.map((preset) => PresetsManager.isValid(preset));
    }

    static getRange(key, mode = 'single') {

        const handler = PRESET_HANDLERS[key];

        if (!PresetsManager.isValid(key)) {
            console.error(`Preset "${key}" is invalid`)
        }

        const result = handler();

        if (!result) {
            if (key === 'custom') return null;
            return null;
        }

        const [start, end] = result;

        return this.formatRange(start, end, mode);
    }

    static formatRange(start, end, mode) {
        if (mode === 'single') {
            const value = end ?? start;
            return value ? value.toISODateString() : null;
        }

        return {
            start: start ? start.toISODateString() : null,
            end: end ? end.toISODateString() : null,
        };
    }

    static isValid(key) {
        return Object.hasOwnProperty.call(PRESET_HANDLERS, key);
    }
}
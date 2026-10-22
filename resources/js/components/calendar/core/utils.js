/**
 * Get short weekday names for a given locale
 * @param {string} locale - BCP 47 language tag
 * @param {number} [startDay=0] - Day of week to start at (0-6)
 * @returns {string[]} Array of 7 short weekday names
 */
export function weekdaysShort(locale, startDay = 0) {

    const formatter = new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone: 'UTC' });

    // we use a known Sunday as starting point (2024-01-07)
    const base = new Date(Date.UTC(2024, 0, 7));

    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(base);

        const dayIndex = (startDay + i) % 7;

        d.setUTCDate(base.getUTCDate() + dayIndex);

        return formatter.format(d);
    });
}

/**
 * Get short month names for a given locale
 * @param {string} locale - BCP 47 language tag
 * @returns {string[]} Array of 12 short month names
 */
export function monthsShort(locale) {
    const formatter = new Intl.DateTimeFormat(locale, { month: 'short', timeZone: 'UTC' });

    return Array.from({ length: 12 }, (_, i) =>
        formatter.format(new Date(Date.UTC(2024, i, 1)))
    );
}

/**
 * Resolve the first day of the week for a given locale
 * Attempts to use Intl.Locale.getWeekInfo() with fallback to weekInfo accessor,
 * finally defaulting to 7 (Sunday) if neither is available
 * @param {string} locale - BCP 47 language tag
 * @returns {number} Day of week (0=Sunday, 1=Monday, ..., 6=Saturday)
 */
export function resolveStartDay(locale) {
    // Intl.Locale.prototype.getWeekInfo() has limited browser availability.
    // it was originally an accessor property (locale.weekInfo) but was changed
    // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/getWeekInfo#browser_compatibility
    // to a method (locale.getWeekInfo()) to fix the identity bug where
    // locale.weekInfo === locale.weekInfo would return false since it returned
    // a new object on each access. We support both forms + a fallback to 7 (Sunday).
    // Firefox does not support getWeekInfo() yet, but the fallback is acceptable
    // since weekFirstDay can always be passed explicitly as an override.
    let resolver = new Intl.Locale(locale);

    return (
        resolver.getWeekInfo?.()?.firstDay
        ?? resolver.weekInfo?.firstDay
        ?? 7
    ) % 7;
}


// Builds an array of years for the dropdown.
// Supports both absolute ranges [2020, 2030] and relative offsets [-10, +10].
// Offsets are detected when both values have absolute value <= 100.
export function buildYears(range, relativeYear) {
    if (!Array.isArray(range) || range.length !== 2) {
        throw new Error('Invalid years range configuration. Expected an array of two numbers.');
    }

    let [start, end] = range;
    const currentYear = relativeYear;

    // If both numbers are small (likely offsets), treat as relative to current year.
    if (Math.abs(start) <= 100 && Math.abs(end) <= 100) {
        start = currentYear + start;
        end = currentYear + end;
    }

    if (start > end) [start, end] = [end, start];

    const years = [];
    for (let year = start; year <= end; year++) {
        years.push(year);
    }
    return years;
}


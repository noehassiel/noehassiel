import DateValue from './DateValue';

/**
 * Attaches an interactive date input mask to a text input element
 * Provides segment-based date entry with keyboard navigation, validation, and auto-advancement
 *
 * @param {Object} config - Configuration object
 * @param {HTMLInputElement} config.input - The input element to attach the mask to
 * @param {AbortSignal} config.abortSignal - Signal for cleanup (removes event listeners)
 * @param {Function} config.onChange - Callback fired when a complete date is entered (receives ISO string)
 * @param {string} config.separator - Separator character between date segments (e.g., '-' for 'yyyy-mm-dd')
 * @returns {Function} Cleanup function to reset internal state (call on destroy)
 *
 * @description
 * The mask creates three segments: year (4 digits), month (2 digits), day (2 digits).
 * Users can:
 * - Type digits to fill segments (auto-advances to next segment when full or value exceeds max)
 * - Use Arrow keys (Left/Right) to navigate between segments
 * - Use Arrow keys (Up/Down) to increment/decrement segment values with rollover for days
 * - Use Backspace/Delete to clear the current segment
 * - The mask normalizes day values when month/year changes
 * - onChange is called on blur or when a complete date is entered via arrow keys
 */
export const attachInputMask = ({
    input,
    abortSignal,
    onChange,
    separator
}) => {

    let FORMAT = {
        order: ['year', 'month', 'day'],
        year: { len: 4, min: 1900, max: 2100, placeholder: 'yyyy' },
        month: { len: 2, min: 1, max: 12, placeholder: 'mm' },
        day: { len: 2, min: 1, max: 31, placeholder: 'dd' },
    };

    /**
     * SEGMENTS object maps segment names to their position in the input string
     * Computed at initialization to account for separator length
     * @type {Object<string, {len: number, start: number, end: number, min: number, max: number, placeholder: string}>}
     */
    const SEGMENTS = {};

    let cursor = 0;
    FORMAT.order.forEach((name, index) => {
        const seg = FORMAT[name];

        SEGMENTS[name] = {
            ...seg,
            start: cursor,
            end: cursor + seg.len,
        };

        cursor += seg.len;

        if (index < FORMAT.order.length - 1) {
            cursor += separator.length;
        }
    });

    const ORDER = FORMAT.order;

    /**
     * Build an empty placeholder string (e.g., "yyyy-mm-dd")
     * @private
     * @returns {string} Placeholder string with separators
     */
    const buildEmptyValue = () => ORDER.map(s => SEGMENTS[s].placeholder).join(separator);

    /**
     * Select (highlight) a segment in the input field
     * Used to visually show which segment is being edited
     * @private
     * @param {string} segment - Segment name ('year', 'month', 'day')
     * @returns {void}
     */
    const selectSegment = (segment) => {

        const s = SEGMENTS[segment];

        requestAnimationFrame(() => {
            input.setSelectionRange(s.start, s.end);
        });
    };

    /**
     * Determine which segment the cursor is currently in based on position
     * @private
     * @param {number} pos - Cursor position in the input
     * @returns {string} Segment name ('year', 'month', or 'day')
     */
    const getSegmentName = (pos) => {
        return ORDER.find(name => pos >= SEGMENTS[name].start && pos <= SEGMENTS[name].end) ?? ORDER[0];
    };

    /**
     * Get the current value of a segment from the input
     * @private
     * @param {string} segment - Segment name ('year', 'month', 'day')
     * @returns {string} Current segment value (may be placeholder)
     */
    const getSegmentValue = (segment) => {
        const s = SEGMENTS[segment];
        return input.value.slice(s.start, s.end);
    };

    /**
     * Update a segment value and reposition cursor within that segment
     * Pads with leading zeros and truncates to segment length
     * @private
     * @param {string} segment - Segment name ('year', 'month', 'day')
     * @param {number|string} value - New value to set
     * @returns {void}
     */
    const updateSegment = (segment, value) => {

        const s = SEGMENTS[segment];

        const left = input.value.slice(0, s.start);
        const right = input.value.slice(s.end);

        const v = String(value).slice(0, s.len).padStart(s.len, '0');

        input.value = left + v + right;

        selectSegment(segment);
    };

    /**
     * Check if a string value is a valid number
     * @private
     * @param {*} v - Value to check
     * @returns {boolean} True if value is a non-empty, valid number
     */
    const isNumber = (v) => v !== '' && v != null && !isNaN(Number(v));

    /**
     * Parse all segments and return current date values
     * Returns null for empty or invalid segments
     * @private
     * @returns {Object} Object with year, month, day properties (number or null)
     */
    const getDateState = () => {
        const state = {};

        ORDER.forEach(name => {
            const v = getSegmentValue(name);
            state[name] = isNumber(v) ? Number(v) : null;
        });

        return state;
    };

    /**
     * Get the maximum valid day for the current month/year context
     * Used to clamp day values when month/year change (e.g., 31st of Feb → 28th)
     * @private
     * @returns {number} Days in the current month (28-31), defaults to 31 if incomplete date
     */
    const maxDayForContext = () => {
        const { year, month } = getDateState();

        if (!year || !month) return 31;

        const dateValue = new DateValue(year, month, 1);

        return dateValue.getDaysInMonth();
    };

    /**
     * Clamp day value to the maximum days in the current month
     * Called after month/year changes to prevent invalid dates like Feb 31st
     * @private
     * @returns {void}
     */
    const normalizeDay = () => {

        const { day } = getDateState();

        if (!day) return;

        const max = maxDayForContext();

        if (day > max) updateSegment('day', max);
    };

    /**
     * Get today's value for a specific segment
     * Used when incrementing/decrementing a segment that's empty
     * @private
     * @param {string} segment - Segment name ('year', 'month', 'day')
     * @returns {number} Today's value for that segment
     */
    const getTodaySegmentValue = (segment) => {

        let dateValue = DateValue.today();
        if (segment === 'year') return dateValue.getYear();
        if (segment === 'month') return dateValue.getMonth();
        if (segment === 'day') return dateValue.getDay();
    }

    /**
     * Commit the current date to the backend via onChange callback
     * Only called when all three segments (year, month, day) are valid numbers
     * @private
     * @returns {void}
     */
    const commitChange = () => {
        const { year, month, day } = getDateState();

        if (!year || !month || !day) return;

        const dateValue = DateValue.fromParts(year, month, day);

        const backend = dateValue.toISODateString();

        onChange(backend)
    }

    if (!input.value) {
        input.value = buildEmptyValue();
    }

    /**
     * Current active segment being edited (updated on focus/click)
     * @private
     * @type {string|undefined}
     */
    let activeSegment;

    /**
     * Buffer for the current segment's typed characters
     * Used for smart advancement (e.g., typing '2' in month auto-advances at '25')
     * @private
     * @type {string}
     */
    let buffer = '';

    /**
     * Sync the active segment based on cursor position
     * Called on focus and click events to ensure correct segment is selected
     * @private
     * @returns {void}
     */
    const syncActive = () => {
        requestAnimationFrame(() => {
            const pos = input.selectionStart ?? 0;
            activeSegment = getSegmentName(pos);
            selectSegment(activeSegment);
        })
    };

    /**
     * Setup: Initialize input value to placeholder if empty
     * Setup: Attach focus and click listeners to track active segment
     */
    ['focus', 'click'].forEach(evt =>
        input.addEventListener(evt, syncActive, { signal: abortSignal })
    );

    /**
     * Setup: Commit the current date when user leaves the input (blur)
     */
    input.addEventListener('blur', () => {
        commitChange();
    }, { signal: abortSignal });

    /**
     * Handle keydown events for date entry and navigation
     * Behaviors:
     * - Number keys (0-9): Fill the active segment, auto-advance to next when full
     * - Backspace/Delete: Clear the active segment
     * - ArrowLeft/Right: Navigate between segments
     * - ArrowUp/Down: Increment/decrement the segment value with rollover for days
     */
    input.addEventListener('keydown', (e) => {
        activeSegment = getSegmentName(input.selectionStart ?? 0);

        if (e.key >= '0' && e.key <= '9') {
            e.preventDefault();

            const seg = SEGMENTS[activeSegment];

            buffer = buffer.length >= seg.len ? e.key : buffer + e.key;

            updateSegment(activeSegment, buffer);

            const num = Number(buffer);
            let shouldAdvance = false;

            if (activeSegment === 'month') {

                if (buffer.length === 1 && num > 1) {
                    shouldAdvance = true;
                } else if (buffer.length === 2) {
                    shouldAdvance = true;
                }
            }

            else if (activeSegment === 'day') {
                const max = maxDayForContext();

                if (buffer.length === 1 && num > Math.floor(max / 10)) {
                    shouldAdvance = true;
                } else if (buffer.length === 2) {
                    shouldAdvance = true;
                }
            }

            else if (activeSegment === 'year') {
                shouldAdvance = false;
            }

            if (shouldAdvance) {
                buffer = '';
                const i = ORDER.indexOf(activeSegment);
                const next = ORDER[i + 1];

                if (next) {
                    activeSegment = next;
                    selectSegment(next);
                }

                normalizeDay();
            }

            return;
        }


        if (e.key === 'Backspace' || e.key === 'Delete') {

            e.preventDefault();

            buffer = '';

            updateSegment(activeSegment, SEGMENTS[activeSegment].placeholder);

            return;
        }

        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            e.preventDefault();
            buffer = '';

            const i = ORDER.indexOf(activeSegment);
            const next = e.key === 'ArrowLeft' ? ORDER[i - 1] : ORDER[i + 1];

            if (next) {
                activeSegment = next;
                selectSegment(next);
            }
        }

        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();

            const dir = e.key === 'ArrowUp' ? 1 : -1;
            const rawValue = getSegmentValue(activeSegment);

            // Special handling for day segment with rollover
            if (activeSegment === 'day') {
                const { year, month, day } = getDateState();
                const allEmpty = !isNumber(year) && !isNumber(month) && !isNumber(day);

                const today = DateValue.today();

                const currentYear = isNumber(year) ? year : today.getYear();
                const currentMonth = isNumber(month) ? month : today.getMonth();
                const currentDay = isNumber(day) ? day : today.getDay();

                let currentDate = new DateValue(currentYear, currentMonth, currentDay);

                let newDate;

                if (allEmpty) {
                    // First press on completely empty field → set to today (no increment)
                    newDate = currentDate;
                } else {
                    newDate = dir === 1 ? currentDate.addDay() : currentDate.subDay();
                }

                updateSegment('year', newDate.getYear());
                updateSegment('month', newDate.getMonth());
                updateSegment('day', newDate.getDay());

                normalizeDay();
                commitChange();
                return;
            }

            // For year and month segments no days rollover needed, they stay within bounds...
            let currentNumber;
            if (isNumber(rawValue)) {
                currentNumber = Number(rawValue);
            } else {
                currentNumber = getTodaySegmentValue(activeSegment);
            }

            let next = currentNumber + dir;
            const seg = SEGMENTS[activeSegment];

            next = Math.min(seg.max, Math.max(seg.min, next));

            updateSegment(activeSegment, next);

            // If month or year changed, re-normalize day
            if (activeSegment === 'month' || activeSegment === 'year') {
                normalizeDay();
            }

            // Commit the change
            commitChange();
        }
    },
        { signal: abortSignal }
    );

    /**
     * Block native beforeinput behavior to prevent double-input from browser spell-checking
     */
    input.addEventListener('beforeinput', (e) => { e.preventDefault() }, { signal: abortSignal });

    /**
     * Return cleanup function - clears buffers and resets state
     * Call this when the component is destroyed or input is removed from DOM
     * @returns {void}
     */
    return () => {
        buffer = '';
        activeSegment = undefined;
    };
};

export default attachInputMask;

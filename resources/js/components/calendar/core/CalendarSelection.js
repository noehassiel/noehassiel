import DateValue from './DateValue';

/**
 * Handles calendar selection logic for single, multiple, and range modes
 * Manages selection state transitions and validation against constraints
 */
export default class CalendarSelection {

    /**
     * Select a day in the current selection mode
     * @static
     * @param {Object} day - Day object
     * @param {Array|string|null} currentState - Current selection state
     * @param {string} mode - Selection mode ('single', 'multiple', 'range')
     * @returns {Object} Selection result with new state and optional constraints
     */
    static select(day, currentState, mode) {

        if (mode === 'single') {
            return { state: this.selectSingle(day) };
        }

        if (mode === 'multiple') {
            return { state: this.selectMultiple(day, currentState) };
        }

        if (mode === 'range') {
            const state = this.selectRange(day, currentState);
            const hasStart = state.hasOwnProperty('start');
            const hasEnd = state.hasOwnProperty('end');

            // only partial selection (start selected, waiting end)
            if (hasStart && !hasEnd) {
                const start = DateValue.fromISOString(state.start);

                return {
                    state,
                    applyRangeConstraints: (months, minRange, maxRange) => CalendarSelection.applyRangeConstraintsToMonths(months, start, minRange, maxRange),
                    shouldResetCalendar: false
                };
            }

            return {
                state,
                shouldResetCalendar: true
            };
        }

        return currentState;
    }

    /**
     * Generate initial base state for calendar display
     * @static
     * @param {string} mode - Selection mode
     * @param {Array|string|null} state - Current selection state
     * @returns {DateValue} Date to display as base month
     */
    static generateBaseState(mode, state, openToDate, forceOpenTo) {

        const today = DateValue.today();

        const openTo = openToDate ? DateValue.fromISOString(openToDate) : null;

        // forceOpenTo always overrides everything, including a selected date
        if (forceOpenTo && openTo) return openTo;

        // no selection: fall back to openTo, then today
        if (!state) return openTo ?? today;

        if (mode === 'single') {
            return DateValue.fromISOString(state) || today;
        }

        if (mode === 'range') {
            if (typeof state === 'object' && state.start) {
                return DateValue.fromISOString(state.start);
            }

            return openTo ?? today;
        }

        if (mode === 'multiple') {

            if (Array.isArray(state) && state.length > 0) {

                const validDates = state
                    .filter(Boolean)
                    .map(DateValue.fromISOString)
                    .filter(Boolean);

                if (validDates.length > 0) {
                    return validDates.reduce((earliest, current) => {
                        return current.isBefore(earliest) ? current : earliest;
                    });
                }
            }

            return openTo ?? today;
        }

        return openTo ?? today;
    }

    /**
     * Select a single day
     * @static
     * @private
     * @param {Object} day - Day object
     * @returns {string} ISO date string
     */
    static selectSingle(day) {
        return day.dateValue.toISODateString();
    }

    /**
     * Toggle selection of a day in multiple mode
     * @static
     * @private
     * @param {Object} day - Day object
     * @param {Array|null} currentState - Current selection state
     * @returns {Array} Updated selection array
     */
    static selectMultiple(day, currentState) {

        const dateStr = day.dateValue.toISODateString();

        let dates = Array.isArray(currentState) ? [...currentState] : [];

        const index = dates.indexOf(dateStr);

        if (index > -1) {
            dates.splice(index, 1);
        } else {
            dates.push(dateStr);
        }

        return dates;
    }

    /**
     * Select or update range endpoint
     * @static
     * @private
     * @param {Object} day - Day object
     * @param {Array|null} currentState - Current selection state ([start] or [start, end])
     * @returns {Array} Updated range array
     */
    static selectRange(day, currentState) {
        const dateStr = day.dateValue.toISODateString();

        if (!currentState || typeof currentState !== 'object') {
            return { start: dateStr };
        }

        const hasStart = currentState.hasOwnProperty('start');
        const hasEnd = currentState.hasOwnProperty('end');

        // If both exist (and end is not null/undefined), reset
        if (hasStart && hasEnd && currentState.end !== null && currentState.end !== undefined) {
            return { start: dateStr };
        }

        // If we have a start but no end, set end
        if (hasStart && (!hasEnd || currentState.end === null)) {
            const startDate = DateValue.fromISOString(currentState.start);
            if (day.dateValue.isBefore(startDate)) {
                return { start: dateStr, end: currentState.start };
            } else {
                return { start: currentState.start, end: dateStr };
            }
        }
        // Otherwise, set start
        return { start: dateStr };
    }

    /**
     * Build cached selection state for fast lookups
     * @static
     * @param {Array|string|null} state - Selection state
     * @param {string} mode - Selection mode
     * @returns {Object|null} Cache object with optimized lookup data
     */
    static buildCache(state, mode) {

        CalendarSelection.checkIfStateValid(state, mode);

        if (mode === 'single') {
            return state
                ? {
                    type: 'single',
                    value: DateValue.toISODateString(state)
                } : null;
        }

        if (mode === 'multiple') {
            return {
                type: 'multiple',
                values: new Set(Array.isArray(state) ? state : [])
            };
        }

        if (mode === 'range') {

            if (!(typeof state === 'object') || Object.keys(state ?? {}).length === 0) return null;

            const start = DateValue.fromISOString(state.start);
            const end = state.end ? DateValue.fromISOString(state.end) : null;

            return {
                type: 'range',
                start, /** @DateValue */
                end, /** @DateValue */
                startTs: start?.getTimestamp(),
                endTs: end ? end.getTimestamp() : null
            };
        }

        return null;
    }

    /**
     * Check if range selection has a start date
     * @static
     * @param {Object|null} cache - Cache object
     * @returns {boolean}
     */
    static hasStart(cache) {
        return !!(cache && cache.type === 'range' && cache.start);
    }

    /**
     * Check if range selection has an end date
     * @static
     * @param {Object|null} cache - Cache object
     * @returns {boolean}
     */
    static hasEnd(cache) {
        return !!(cache && cache.type === 'range' && cache.end);
    }

    /**
     * Check if a date is currently selected
     * @static
     * @param {string} dateStr - ISO date string
     * @param {Object|null} cache - Cache object
     * @returns {boolean}
     */
    static isSelected(dateStr, cache) {

        if (!cache) return false;

        if (cache.type === 'single') {
            return cache.value === dateStr;
        }

        if (cache.type === 'multiple') {
            return cache.values.has(dateStr);
        }

        if (cache.type === 'range') {
            return (CalendarSelection.hasStart(cache) ? dateStr === cache.start.toISODateString() : false)
                || (CalendarSelection.hasEnd(cache) ? dateStr === cache.end.toISODateString() : false);
        }

        return false;
    }

    /**
     * Check if timestamp is between range endpoints (exclusive)
     * @static
     * @param {number} timestamp - Milliseconds since epoch
     * @param {Object|null} cache - Cache object
     * @returns {boolean}
     */
    static isBetween(day, cache) {

        if (!cache || cache.type !== 'range') return false;
        if (!cache.endTs) return false;

        const timestamp = day.dateValue.getTimestamp();

        return timestamp > cache.startTs && timestamp < cache.endTs;
    }

    /**
     * Check if timestamp is in range preview (between start and hovered day, exclusive)
     * @static
     * @param {number} timestamp - Milliseconds since epoch
     * @param {Object|null} cache - Cache object
     * @param {number|null} hoveredTs - Hovered day timestamp
     * @returns {boolean}
     */
    static isHoverPreview(day, cache, hoveredTs) {

        if (!cache || cache.type !== 'range') return false;
        if (cache.endTs) return false;
        if (!hoveredTs) return false;

        const min = Math.min(cache.startTs, hoveredTs);
        const max = Math.max(cache.startTs, hoveredTs);

        let timestamps = day.dateValue.getTimestamp();

        return timestamps > min && timestamps < max;
    }

    /**
     * Check if timestamp is the hovered range end point
     * @static
     * @param {number} timestamp - Milliseconds since epoch
     * @param {Object|null} cache - Cache object
     * @param {number|null} hoveredDayTs - Hovered day timestamp
     * @returns {boolean}
     */
    static isHoverRangeEnd(timestamp, cache, hoveredDayTs) {

        if (!cache || cache.type !== 'range') return false;
        if (cache.endTs) return false;
        if (!hoveredDayTs) return false;

        return timestamp === hoveredDayTs;
    }


    /**
     * Get today's date in the appropriate format for selection mode
     * @static
     * @param {string} mode - Selection mode ('single', 'multiple', 'range')
     * @returns {string|Array}
     */
    static selectToday(mode) {

        const today = DateValue.today().toISODateString();

        if (mode === 'single') return today;
        if (mode === 'multiple') return [today];
        if (mode === 'range') return { start: today, end: today };

        return today;
    }

    static applyRangeConstraintsToMonths(months, start, minRange, maxRange) {
        return months.map((month) => {
            month.days.forEach((d) => {
                const dayValue = DateValue.fromISOString(d.dateValue.toISODateString());
                const daysDiff = Math.abs(start.diffDays(dayValue));

                if (minRange !== null && daysDiff < minRange) {
                    d.isDisabled = true;
                }

                if (maxRange !== null && daysDiff > maxRange) {
                    d.isDisabled = true;
                }
            });

            return month;
        });
    }

    static isMonthSelectable(year, month, minDate, maxDate) {

        const first = DateValue.fromParts(year, month);
        const last = DateValue.fromParts(year, month, first.getDaysInMonth());

        if (minDate && last.isBefore(minDate)) return false;
        if (maxDate && first.isAfter(maxDate)) return false;

        return true;
    }

    static isYearSelectable(year, minDate, maxDate) {
        const minYear = minDate?.getYear();
        const maxYear = maxDate?.getYear();

        if (minYear !== undefined && year < minYear) return false;
        if (maxYear !== undefined && year > maxYear) return false;

        return true;
    }

    // @todo: this is needs a validation class, it seems really bloated in this place.  
    static checkIfStateValid(state, mode) {
        if (!state) return true;

        let actualType = Array.isArray(state) ? 'array' : state === null ? 'null' : typeof state;

        try {
            if (mode === 'single') {
                if (typeof state !== 'string') {
                    console.error(`[CalendarSelection] single mode expects a string, got ${actualType}`);
                    return false;
                }

                if (!DateValue.isValid(state)) {
                    console.error(`[CalendarSelection] invalid date string in single mode: "${state}"`);
                    return false;
                }

                return true;
            }

            if (mode === 'range') {
                if (typeof state !== 'object' || Array.isArray(state)) {
                    console.error(`[CalendarSelection] range mode expects an object { start, end }, got ${actualType}`);
                    return false;
                }

                if (state.start && !DateValue.isValid(state.start)) {
                    console.error(`[CalendarSelection] invalid start date in range mode: "${state.start}"`);
                    return false;
                }

                if (state.end && !DateValue.isValid(state.end)) {
                    console.error(`[CalendarSelection] invalid end date in range mode: "${state.end}"`);
                    return false;
                }

                // start must come before or equal to end
                if (state.start && state.end) {
                    const start = DateValue.fromISOString(state.start);
                    const end = DateValue.fromISOString(state.end);

                    if (end.isBefore(start)) {
                        console.error(`[CalendarSelection] range end "${state.end}" is before start "${state.start}"`);
                        return false;
                    }
                }

                return true;
            }

            if (mode === 'multiple') {
                if (!Array.isArray(state)) {
                    console.error(`[CalendarSelection] multiple mode expects an array, got ${actualType}`);
                    return false;
                }

                const invalidDates = state.filter(d => !DateValue.isValid(d));
                if (invalidDates.length > 0) {
                    console.error(`[CalendarSelection] invalid date strings in multiple mode:`, invalidDates);
                    return false;
                }

                return true;
            }

        } catch (e) {
            console.error(`[CalendarSelection] state validation threw unexpectedly:`, e);
            return false;
        }

        return true;
    }
}
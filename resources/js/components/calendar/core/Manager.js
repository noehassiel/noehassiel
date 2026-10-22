import Month from './Month';
import DateValue from './DateValue';

/**
 * Manages calendar rendering, navigation, and day selection
 */
class Manager {

    /**
     * Navigate to next month
     * @static
     * @param {DateValue} baseDate - Current base date
     * @returns {DateValue} New base date
     */
    static nextMonth(baseDate) {
        return baseDate.addMonth();
    }

    /**
     * Navigate to previous month
     * @static
     * @param {DateValue} baseDate - Current base date
     * @returns {DateValue} New base date
     */
    static prevMonth(baseDate) {
        return baseDate.subMonth();
    }

    /**
     * Navigate to today
     * @static
     * @returns {DateValue} Current date
     */
    static goToToday() {
        return DateValue.today();
    }

    /**
     * Render one or more months with days and blanks
     * @static
     * @param {Object} options - Render options
     * @param {DateValue} options.baseDate - Starting month to render
     * @param {number} options.numberOfMonths - How many months to render
     * @param {boolean} [options.fixedWeeks] - Whether to use fixed week heights
     * @param {string|null} [options.min] - Minimum date constraint as ISO string
     * @param {string|null} [options.max] - Maximum date constraint as ISO string
     * @param {string[]} [options.unavailableDates] - Array of unavailable dates
     * @param {string} [options.locale] - BCP 47 language tag
     * @returns {Array} Array of month objects with days and blanks
     */
    static render({ baseDate, numberOfMonths, fixedWeeks = false, min = undefined, max = undefined, unavailableDates = [], locale = undefined, startDay = undefined, specialDays = {}, specialDisabled = [], specialTooltips = {}, }) {
        const rendered = [];

        const maxWeeks = fixedWeeks ? this.calculateMinWeeksBetweenRenderedMonths(baseDate.getYear(), baseDate.getMonth(), numberOfMonths, startDay) : null;

        for (let i = 0; i < numberOfMonths; i++) {

            const date = baseDate.addMonths(i);

            const month = new Month(date, maxWeeks, min, max, unavailableDates, locale, startDay, specialDays, specialDisabled, specialTooltips);

            rendered.push(month.toPlainObject());
        }

        return rendered;
    }

    /**
     * Move focus one day forward (ArrowRight), skipping disabled days
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @param {number} numberOfMonths - Number of rendered months
     * @returns {Object} { key, baseDate } target timestamp and (possibly slid) base date
     */
    static nextDay(focused, baseDate, navigationIndex, numberOfMonths) {
        return this.#stepForward(focused.dateValue.addDay(), baseDate, navigationIndex, numberOfMonths, false);
    }

    /**
     * Move focus one day backward (ArrowLeft), skipping disabled days
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @returns {Object} { key, baseDate } target timestamp and (possibly slid) base date
     */
    static previousDay(focused, baseDate, navigationIndex) {
        return this.#stepBackward(focused.dateValue.subDay(), baseDate, navigationIndex, false);
    }

    /**
     * Move focus to the same weekday one week ahead (ArrowDown)
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @param {number} numberOfMonths - Number of rendered months
     * @returns {Object} { key, baseDate } target timestamp and (possibly slid) base date
     */
    static nextDayInNextWeek(focused, baseDate, navigationIndex, numberOfMonths) {
        return this.#stepForward(focused.dateValue.addWeek(), baseDate, navigationIndex, numberOfMonths, true);
    }

    /**
     * Move focus to the same weekday one week back (ArrowUp)
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @returns {Object} { key, baseDate } target timestamp and (possibly slid) base date
     */
    static previousDayInPreviousWeek(focused, baseDate, navigationIndex) {
        return this.#stepBackward(focused.dateValue.subWeek(), baseDate, navigationIndex, true);
    }

    /**
     * Move focus to the first day of the focused week (Home key).
     * Snaps forward to the first enabled day when the week start is disabled
     * or falls on a blank cell. Never slides the visible window.
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @param {number} startDay - First day of week (0=Sunday … 6=Saturday)
     * @returns {Object} { key, baseDate }
     */
    static startOfWeek(focused, baseDate, navigationIndex, startDay) {
        const offset = (focused.dateValue.getDayOfWeek() - startDay + 7) % 7;
        const target = focused.dateValue.subDays(offset);

        const found = navigationIndex.nearestOnOrAfter(target.getTimestamp());

        return { key: found ?? focused.dateValue.getTimestamp(), baseDate };
    }

    /**
     * Move focus to the last day of the focused week (End key).
     * Snaps backward to the last enabled day when the week end is disabled
     * or falls on a blank cell. Never slides the visible window.
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @param {number} startDay - First day of week (0=Sunday … 6=Saturday)
     * @returns {Object} { key, baseDate }
     */
    static endOfWeek(focused, baseDate, navigationIndex, startDay) {
        const offset = (focused.dateValue.getDayOfWeek() - startDay + 7) % 7;
        const target = focused.dateValue.addDays(6 - offset);

        const found = navigationIndex.nearestOnOrBefore(target.getTimestamp());

        return { key: found ?? focused.dateValue.getTimestamp(), baseDate };
    }

    /**
     * Move focus to the same day in the previous month (PageUp key).
     * The day is clamped to the target month's length (Mar 31 → Feb 28).
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @returns {Object} { key, baseDate } target timestamp and (possibly slid) base date
     */
    static sameDayPreviousMonth(focused, baseDate, navigationIndex) {
        return this.#stepBackward(this.#addMonthsClamped(focused.dateValue, -1), baseDate, navigationIndex, false);
    }

    /**
     * Move focus to the same day in the next month (PageDown key).
     * The day is clamped to the target month's length (Jan 31 → Feb 28).
     * @static
     * @param {Object} focused - Focused day object (has .dateValue)
     * @param {DateValue} baseDate - Current base date
     * @param {NavigationIndex} navigationIndex - Index of enabled days in the rendered window
     * @param {number} numberOfMonths - Number of rendered months
     * @returns {Object} { key, baseDate } target timestamp and (possibly slid) base date
     */
    static sameDayNextMonth(focused, baseDate, navigationIndex, numberOfMonths) {
        return this.#stepForward(this.#addMonthsClamped(focused.dateValue, 1), baseDate, navigationIndex, numberOfMonths, false);
    }

    /**
     * Month arithmetic that clamps the day instead of overflowing
     * (unlike DateValue.addMonths, where Jan 31 + 1 month = Mar 3).
     */
    static #addMonthsClamped(dateValue, delta) {
        const firstOfTarget = dateValue.startOfMonth().addMonths(delta);

        return DateValue.fromParts(
            firstOfTarget.getYear(),
            firstOfTarget.getMonth(),
            Math.min(dateValue.getDay(), firstOfTarget.getDaysInMonth())
        );
    }

    /**
     * Resolve a forward navigation target against the rendered window.
     *
     * Week moves (sameWeekday=true) preserve the weekday column: when the exact
     * target is disabled, the same weekday on following weeks is tried. Day moves
     * snap to the nearest enabled day at or after the target. When no enabled day
     * remains inside the rendered window, the window slides one month forward and
     * the original target becomes the focus key (it is focused after re-render,
     * unless disabled there too).
     *
     * The target is computed with real date math, so disabled days can never
     * shift which weekday a week-move lands on.
     */
    static #stepForward(target, baseDate, navigationIndex, numberOfMonths, sameWeekday) {
        const windowEnd = baseDate.addMonths(numberOfMonths - 1).lastOfMonth();

        if (!target.isAfter(windowEnd)) {
            if (sameWeekday) {
                for (let candidate = target; !candidate.isAfter(windowEnd); candidate = candidate.addWeek()) {
                    if (navigationIndex.has(candidate.getTimestamp())) {
                        return { key: candidate.getTimestamp(), baseDate };
                    }
                }
            } else {
                const found = navigationIndex.nearestOnOrAfter(target.getTimestamp());

                if (found !== null) {
                    return { key: found, baseDate };
                }
            }
        }

        return { key: target.getTimestamp(), baseDate: this.nextMonth(baseDate) };
    }

    /**
     * Backward mirror of #stepForward (window start bound, previous month slide).
     */
    static #stepBackward(target, baseDate, navigationIndex, sameWeekday) {
        const windowStart = baseDate.startOfMonth();

        if (!target.isBefore(windowStart)) {
            if (sameWeekday) {
                for (let candidate = target; !candidate.isBefore(windowStart); candidate = candidate.subWeek()) {
                    if (navigationIndex.has(candidate.getTimestamp())) {
                        return { key: candidate.getTimestamp(), baseDate };
                    }
                }
            } else {
                const found = navigationIndex.nearestOnOrBefore(target.getTimestamp());

                if (found !== null) {
                    return { key: found, baseDate };
                }
            }
        }

        return { key: target.getTimestamp(), baseDate: this.prevMonth(baseDate) };
    }

    /**
     * Calculate the maximum number of weeks needed across a range of months
     * Used for fixed-height calendar grids
     * @static
     * @param {number} year - Starting year
     * @param {number} month - Starting month (1-12)
     * @param {number} numberOfMonths - Number of months to check
     * @returns {number} Maximum weeks needed
     */
    static calculateMinWeeksBetweenRenderedMonths(year, month, numberOfMonths, startDay) {
        let maxWeeks = 0;

        for (let i = 0; i < numberOfMonths; i++) {
            const date = DateValue.fromParts(year, month).addMonths(i);

            maxWeeks = Math.max(maxWeeks, date.getWeeksInMonth(startDay));
        }

        return maxWeeks;
    }

    static canNavigateTo(baseDate, options) {

        const { numberOfMonths, minDate, maxDate, rangeOptions } = options;

        const firstDay = baseDate.startOfMonth();
        const lastDay = baseDate.addMonths(numberOfMonths - 1).lastOfMonth();

        // standard min/max date constraints
        if (minDate && lastDay.isBefore(minDate)) return false;
        if (maxDate && firstDay.isAfter(maxDate)) return false;

        // range maxRange navigation constraint
        // when user has selected range start, block navigation to months
        // that are entirely outside the reachable window from that start date
        if (rangeOptions) {
            const { firstSelected, maxRange } = rangeOptions;

            if (firstSelected && maxRange !== null) {
                // farthest forward the user can select from the start
                const maxForward = firstSelected.addDays(maxRange);
                // farthest backward the user can select from the start  
                const maxBackward = firstSelected.subDays(maxRange);

                // the candidate month is entirely beyond forward reach
                if (firstDay.isAfter(maxForward)) return false;

                // the candidate month is entirely before backward reach
                if (lastDay.isBefore(maxBackward)) return false;
            }
        }

        return true;
    }
}

export default Manager;
import Day from "./Day";
import BlankDay from "./BlankDay";
import DateValue from "./DateValue";

/**
 * Month class - Represents a month in the calendar with generated days and blanks
 *
 * Manages the generation of a calendar month grid, including:
 * - Pre-blank days (days from previous month filling the start of the week)
 * - Actual days of the current month
 * - Post-blank days (days from next month filling the end of the week)
 *
 * Handles validation against min/max dates and unavailable dates to mark days as disabled or unavailable.
 *
 * @class Month
 * @example
 * const month = new Month(
 *   DateValue.now(),
 *   5,
 *   '2026-01-01',
 *   '2026-12-31',
 *   ['2026-02-14'],
 *   'en-US'
 * );
 * const days = month.days; // Array of Day objects
 */
const Month = class {

    /** @private @type {DateValue|undefined} - Minimum selectable date */
    #min = undefined;
    /** @private @type {DateValue|undefined} - Maximum selectable date */
    #max = undefined;
    /** @private @type {Set<number>} - Hash set of unavailable date timestamps for fast lookup */
    #unavailableDates = [];

    /** @private @type {DateValue} - The date representing this month */
    #date = null;
    /** @private @type {number} - Number of days in this month */
    #numOfDays = null;
    /** @private @type {BlankDay[]} - Days from previous month filling the start */
    #preBlanks = [];
    /** @private @type {BlankDay[]} - Days from next month filling the end */
    #postBlanks = [];
    /** @private @type {Day[]} - Actual days of the current month */
    #days = [];
    /** @private @type {number} - Month index (0-11) */
    #monthIndex = undefined;
    /** @private @type {number} - Year value */
    #yearIndex = undefined;
    /** @private @type {number|null} - Fixed number of weeks or null for auto-calculate */
    #maxWeeks = null;
    /** @private @type {string} - BCP 47 language tag for formatting */
    #locale = undefined;

    /** @private @type {string|null} - Formatted month label (e.g., "Jan") */
    #label = null;
    /** @static @type {number|null} - Year value for this month */
    static year = null;

    // localized formatter, cached per month so we don't create
    // a new Intl.DateTimeFormat per day (far more expensive)
    #formatter = undefined;


    // start day

    #startDay = undefined;


    #specialDays = {};
    #specialDisabled = [];

    // special tooltips
    #specialTooltips = {};

    /** @private @type {Array<Array<Day|BlankDay>>|null} */
    #weeksCache = null;
    /**
     * Constructs a Month instance and generates its calendar grid
     *
     * @param {DateValue} currentMonth - The month to represent
     * @param {number|null} maxWeeks - Fixed number of weeks to display (e.g., 5 or 6) or null for auto
     * @param {string|null} min - Minimum selectable date as ISO string (YYYY-MM-DD)
     * @param {string|null} max - Maximum selectable date as ISO string (YYYY-MM-DD)
     * @param {string[]} unavailableDates - Array of unavailable dates as ISO strings
     * @param {string} locale - BCP 47 language tag (e.g., 'en-US', 'fr-FR') for date formatting
     */
    constructor(currentMonth, maxWeeks, min, max, unavailableDates, locale, startDay, specialDays = {}, specialDisabled = [], specialTooltips = {}) {

        this.#date = currentMonth;
        this.#min = min ? DateValue.fromISOString(min) : undefined;
        this.#max = max ? DateValue.fromISOString(max) : undefined;
        this.#unavailableDates = this.#normalizeUnavailableDates(unavailableDates);

        this.#maxWeeks = maxWeeks;
        this.#locale = locale;

        this.#startDay = startDay;

        this.#numOfDays = this.#date.getDaysInMonth();

        // indices
        this.#yearIndex = this.#date.getYear();
        this.#monthIndex = this.#date.getMonth();

        this.#label = Intl.DateTimeFormat(this.#locale, { month: 'short', timeZone: 'UTC' }).format(this.#date.toDate());

        this.#formatter = new Intl.DateTimeFormat(this.#locale, {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC'
        });

        this.#specialDays = this.#normalizeSpecialDays(specialDays);
        this.#specialDisabled = specialDisabled;
        this.#specialTooltips = specialTooltips;

        this.generate();
    }

    /**
     * Converts array of ISO date strings to a Set of timestamps for O(1) lookup
     *
     * @private
     * @param {string[]} dates - Array of ISO date strings
     * @returns {Set<number>} Set of timestamps for fast availability checks
     */
    #normalizeUnavailableDates(dates) {
        if (!Array.isArray(dates) || dates.length === 0) {
            return new Set();
        }

        return new Set(
            dates.map(date => DateValue.fromISOString(date).getTimestamp())
        );
    }

    #normalizeSpecialDays(specialDays) {
        return Object.entries(specialDays).reduce((acc, [key, dates]) => {
            acc[key] = new Set(
                (Array.isArray(dates) ? dates : [])
                    .map(d => DateValue.fromISOString(d)?.getTimestamp())
                    .filter(Boolean)
            );
            return acc;
        }, {});
    }

    #getDayKeys(day) {
        const ts = day.getTimestamp();

        return Object.entries(this.#specialDays)
            .filter(([, set]) => set.has(ts))
            .map(([key]) => key);
    }

    #getDayTooltip(keys) {
        for (const key of keys) {
            if (this.#specialTooltips[key]) return this.#specialTooltips[key];
        }
        
        return null;
    }

    /**
     * Determines if a day is disabled based on min/max date constraints
     *
     * A day is disabled if it falls outside the min/max date range.
     *
     * @private
     * @param {DateValue} day - The day to check
     * @returns {boolean} True if day is before min or after max
     */
    #isDayDisabled(day) {
        // Check if before min
        if (this.#min && day.isBefore(this.#min)) {
            return true;
        }

        // Check if after max
        if (this.#max && day.isAfter(this.#max)) {
            return true;
        }

        if (this.#isDayUnavailable(day)) {
            return true;
        }

        const keys = this.#getDayKeys(day);

        if (keys.some(k => this.#specialDisabled.includes(k))) return true;

        return false;
    }

    /**
     * Checks if a day is in the unavailable dates list
     *
     * Unavailable dates are selectable but may have different styling.
     *
     * @private
     * @param {DateValue} day - The day to check
     * @returns {boolean} True if day is in the unavailable dates set
     */
    #isDayUnavailable(day) {
        return this.#unavailableDates.has(day.getTimestamp());
    }

    /**
     * Generates all calendar cells (pre-blanks, days, post-blanks) for this month
     *
     * Orchestrates the generation of the complete month grid by calling:
     * - generatePreBlankDays() - fill week start
     * - generateDays() - actual month days
     * - generatePostBlankDays() - fill week end
     *
     * @public
     * @returns {void}
     */
    generate() {
        this.#preBlanks = this.generatePreBlankDays();
        this.#days = this.generateDays(this.#preBlanks);
        this.#postBlanks = this.generatePostBlankDays(this.#preBlanks, this.#maxWeeks);
        this.#weeksCache = null;
    }

    get weeks() {
        if (!this.#weeksCache) {
            const all = [...this.#preBlanks, ...this.#days, ...this.#postBlanks];
            const weeks = [];
            for (let i = 0; i < all.length; i += 7) {
                const weekDays = all.slice(i, i + 7);
                const weekNumber = weekDays[0]?.dateValue?.getWeekNumber() ?? null;
                weeks.push({ num: weekNumber, days: weekDays });
            }
            this.#weeksCache = weeks;
        }
        
        return this.#weeksCache;
    }



    /**
     * Converts the month into a plain object representation
     *
     * Useful for serialization or passing to templates/components.
     *
     * @public
     * @returns {Object} Plain object with month data
     * @returns {string} .label - Formatted month name (e.g., "Jan")
     * @returns {number} .year - The year value
     * @returns {number} .month - The month index
     * @returns {Day[]} .days - Days of the current month
     * @returns {BlankDay[]} .preBlanks - Pre-blank days
     * @returns {BlankDay[]} .postBlanks - Post-blank days
     * @returns {number} .weeks - Week count
     */
    toPlainObject() {
        return {
            label: this.#label,
            year: this.#yearIndex,
            month: this.#monthIndex,
            days: this.#days,
            preBlanks: this.#preBlanks,
            postBlanks: this.#postBlanks,
            weeks: this.weeks
        };
    }

    /**
     * Generates blank days from the previous month to fill the calendar start
     *
     * Calculates how many days from the previous month are needed to fill
     * the beginning of the week, then creates BlankDay objects for each.
     *
     * @public
     * @returns {BlankDay[]} Array of BlankDay objects from previous month
     */
    generatePreBlankDays() {

        const firstDayOfMonth = this.#date.startOfMonth();
        const dayOfWeek = (firstDayOfMonth.getDayOfWeek() - this.#startDay + 7) % 7;

        let previousMonth = this.previousMonthMetadata(this.#date);

        const preBlanks = [];

        for (let j = 0; j < dayOfWeek; j++) {
            const dayNum = previousMonth.numberOfDays - dayOfWeek + j + 1;


            const day = DateValue.fromParts(previousMonth.yearNumber, previousMonth.monthNumber, dayNum);

            const isDisabled = this.#isDayDisabled(day);
            const isUnavailable = this.#isDayUnavailable(day);

            preBlanks.push(new BlankDay({
                number: dayNum,
                /**@DateValue */
                dateValue: day,
                label: this.#formatter.format(day.toDate()),
                isDisabled: isDisabled,
                isUnavailable: isUnavailable,
                inEdgeCorner: j === 0,
                inEdgeMiddle: j === dayOfWeek - 1,
                month: previousMonth.monthNumber,
                year: previousMonth.yearNumber,
                type: 'pre'
            }));
        }

        return preBlanks;
    }

    /**
     * Generates Day objects for each day of the current month
     *
     * Creates Day objects with full metadata including position in grid,
     * disabled/unavailable status, and whether it's today.
     *
     * @public
     * @param {BlankDay[]} preBlanks - Pre-blank days to calculate row positions
     * @returns {Day[]} Array of Day objects for the current month
     */
    generateDays(preBlanks) {
        const days = [];

        for (let j = 1; j <= this.#numOfDays; j++) {
            const rowIndex = (preBlanks.length + j - 1) % 7;
            const day = DateValue.fromParts(this.#yearIndex, this.#monthIndex, j);

            const isDisabled = this.#isDayDisabled(day);
            const isUnavailable = this.#isDayUnavailable(day);

            let specialDayKeys = this.#getDayKeys(day);
            days.push(new Day({
                number: j,

                /**@DateValue */
                dateValue: day,
                label: this.#formatter.format(day.toDate()),
                timestamp: day.getTimestamp(),
                isDisabled: isDisabled,
                isUnavailable: isUnavailable,
                isFirstInRow: rowIndex === 0,
                isLastInRow: rowIndex === 6,
                isFirstInMonth: j === 1,
                isLastInMonth: j === this.#numOfDays,
                month: this.#monthIndex,
                year: this.#yearIndex,
                keys: specialDayKeys,
                tooltip: this.#getDayTooltip(specialDayKeys),
            }));
        }

        return days;
    }


    /**
     * Generates blank days from the next month to fill the calendar end
     *
     * Calculates how many days are needed to complete the grid based on
     * the number of pre-blanks, days in month, and desired week count.
     *
     * @public
     * @param {BlankDay[]} preBlanks - Pre-blank days from previous month
     * @param {number|null} minWeeks - Minimum weeks to display or null for auto-calculation
     * @returns {BlankDay[]} Array of BlankDay objects from next month
     */
    generatePostBlankDays(preBlanks, minWeeks) {
        const reservedCells = preBlanks.length + this.#numOfDays;
        let remainingCells;

        if (minWeeks) {
            remainingCells = Math.max(0, minWeeks * 7 - reservedCells);
        } else {
            remainingCells = reservedCells % 7 === 0 ? 0 : 7 - (reservedCells % 7);
        }

        const nextMonth = this.nextMonthMetadata(this.#date);

        const postBlanks = [];

        for (let j = 1; j <= remainingCells; j++) {

            const day = DateValue.fromParts(nextMonth.yearNumber, nextMonth.monthNumber, j);

            const isDisabled = this.#isDayDisabled(day);
            const isUnavailable = this.#isDayUnavailable(day);

            postBlanks.push(new BlankDay({
                number: j,

                /**@DateValue */
                dateValue: day,
                label: this.#formatter.format(day.toDate()),
                isDisabled: isDisabled,
                isUnavailable: isUnavailable,
                inEdgeCorner: j === remainingCells,
                inEdgeMiddle: j === 1,
                month: nextMonth.monthNumber,
                year: nextMonth.yearNumber,
                type: 'post'
            }));
        }

        return postBlanks;
    }

    /**
     * Gets metadata for the next month
     *
     * @public
     * @param {DateValue} currentMonth - The current month to get the next month from
     * @returns {Object} Metadata object for next month
     * @returns {number} .numberOfDays - Days in the next month
     * @returns {number} .monthNumber - Month index of next month
     * @returns {number} .yearNumber - Year of next month
     */
    nextMonthMetadata(currentMonth) {
        const nextMonth = currentMonth.addMonth();

        return {
            numberOfDays: nextMonth.getDaysInMonth(),
            monthNumber: nextMonth.getMonth(),
            yearNumber: nextMonth.getYear()
        }
    }

    /**
     * Gets metadata for the previous month
     *
     * @public
     * @param {DateValue} currentMonth - The current month to get the previous month from
     * @returns {Object} Metadata object for previous month
     * @returns {number} .numberOfDays - Days in the previous month
     * @returns {number} .monthNumber - Month index of previous month
     * @returns {number} .yearNumber - Year of previous month
     */
    previousMonthMetadata(currentMonth) {
        const prevMonth = currentMonth.subMonth();

        return {
            numberOfDays: prevMonth.getDaysInMonth(),
            monthNumber: prevMonth.getMonth(),
            yearNumber: prevMonth.getYear()
        }
    }

}

export default Month;
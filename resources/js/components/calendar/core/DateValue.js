/**
 * Immutable date value wrapper for UTC date handling
 * Uses __date instead of private field to work with Alpine Proxy wrapping
 * See: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Proxy#no_private_field_forwarding
 */
export default class DateValue {

    /**
     * @type {Date}
     * @private
     * Uses __ prefix instead of # because Alpine wraps state in Proxies,
     * and private field access breaks after proxying
     */
    __date;

    static INVALID_DATE_STRING = "Invalid Date";

    /**
     * Create a new DateValue
     * @param {number} year - Year
     * @param {number} month - Month (1-12, 1-based)
     * @param {number} [day=1] - Day of month (1-31)
     */
    constructor(year, month, day = 1) {
        // month is 1-based externally, 0-based internally
        this.__date = new Date(Date.UTC(year, month - 1, day));
    }

    /**
     * Check if this date is the same day as another
     * @param {DateValue|null} other - Date to compare
     * @returns {boolean}
     */
    isSameDay(other) {
        if (!other) return false;
        return this.__date.getUTCFullYear() === other.__date.getUTCFullYear() && this.__date.getUTCMonth() === other.__date.getUTCMonth() && this.__date.getUTCDate() === other.__date.getUTCDate();
    }

    /**
     * Check if this date is before another
     * @param {DateValue|null} other - Date to compare
     * @returns {boolean}
     */
    isBefore(other) {
        if (!other) return false;

        return this.__date < other.__date;
    }

    /**
     * Check if this date is after another
     * @param {DateValue|null} other - Date to compare
     * @returns {boolean}
     */
    isAfter(other) {
        if (!other) return false;

        return this.__date > other.__date;
    }

    /**
     * Check if this date is between two dates (inclusive)
     * @param {DateValue|null} min - Start date or null for no lower bound
     * @param {DateValue|null} max - End date or null for no upper bound
     * @returns {boolean}
     */
    isBetween(min, max) {
        if (!min && !max) return true;
        if (!min) return this.__date <= max.__date;
        if (!max) return this.__date >= min.__date;
        return this.__date >= min.__date && this.__date <= max.__date;
    }

    /**
     * Check if this date is today
     * @returns {boolean}
     */
    isToday() {
        return this.isSameDay(DateValue.today());
    }

    // -------------------------
    // Immutable Math manipulation
    // -------------------------

    /**
     * Add days to this date (returns new DateValue)
     * @param {number} days - Number of days to add
     * @returns {DateValue} New date
     */
    addDays(days) {
        return new DateValue(this.getYear(), this.getMonth(), this.getDay() + days);
    }

    /**
     * Add one day to this date
     * @returns {DateValue} New date
     */
    addDay() {
        return this.addDays(1);
    }

    /**
     * Subtract days from this date
     * @param {number} days - Number of days to subtract
     * @returns {DateValue} New date
     */
    subDays(days) {
        return this.addDays(-days)
    }

    /**
     * Subtract one day from this date
     * @returns {DateValue} New date
     */
    subDay() {
        return this.subDays(1)
    }

    /**
     * Add weeks to this date
     * @param {number} weeks - Number of weeks to add
     * @returns {DateValue} New date
     */
    addWeeks(weeks) {
        return this.addDays(weeks * 7);
    }

    /**
     * Add one week to this date
     * @returns {DateValue} New date
     */
    addWeek() {
        return this.addWeeks(1);
    }

    /**
     * Subtract weeks from this date
     * @param {number} weeks - Number of weeks to subtract
     * @returns {DateValue} New date
     */
    subWeeks(weeks) {
        return this.addDays(-weeks * 7);
    }

    /**
     * Subtract one week from this date
     * @returns {DateValue} New date
     */
    subWeek() {
        return this.subWeeks(1);
    }

    /**
     * Add months to this date (handles month overflow naturally)
     * @param {number} months - Number of months to add
     * @returns {DateValue} New date
     */
    addMonths(months) {
        // let Date handle month overflow naturally (e.g. Jan + 1 = Feb)
        const d = new Date(this.__date);

        d.setUTCMonth(d.getUTCMonth() + months);

        return DateValue.fromUTCDate(d);
    }

    /**
     * Subtract months from this date
     * @param {number} months - Number of months to subtract
     * @returns {DateValue} New date
     */
    subMonths(months) {
        return this.addMonths(-months);
    }

    /**
     * Add one month to this date
     * @returns {DateValue} New date
     */
    addMonth() {
        return this.addMonths(1)
    }

    /**
     * Subtract one month from this date
     * @returns {DateValue} New date
     */
    subMonth() {
        return this.subMonths(1);
    }

    /**
     * Get the first day of this month
     * @returns {DateValue} New date set to first day of month
     */
    startOfMonth() {
        return new DateValue(this.getYear(), this.getMonth(), 1);
    }

    /**
     * Get the last day of this month
     * @returns {DateValue} New date set to last day of month
     */
    lastOfMonth() {
        return new DateValue(this.getYear(), this.getMonth(), this.getDaysInMonth());
    }

    // -------------------------
    // Diff
    // -------------------------

    /**
     * Calculate difference in days from another date
     * @param {DateValue} other - Date to compare
     * @returns {number} Days difference (negative if other is after this)
     */
    diffDays(other) {
        return Math.floor((this.getTimestamp() - other.getTimestamp()) / 86400000);
    }

    /**
     * Calculate absolute difference in days from another date
     * @param {DateValue} other - Date to compare
     * @returns {number} Absolute days difference
     */
    absDiffDays(other) {
        return Math.abs(this.diffDays(other));
    }

    // -------------------------
    // Getters
    // -------------------------

    /**
     * Get year
     * @returns {number}
     */
    getYear() { return this.__date.getUTCFullYear(); }

    /**
     * Get month (1-based)
     * @returns {number} Month 1-12
     */
    getMonth() { return this.__date.getUTCMonth() + 1; }

    /**
     * Get day of month
     * @returns {number} Day 1-31
     */
    getDay() { return this.__date.getUTCDate(); }

    /**
     * Get day of week
     * @returns {number} 0=Sunday, 1=Monday, ..., 6=Saturday
     */
    getDayOfWeek() { return this.__date.getUTCDay(); }

    /**
     * Get number of days in this month
     * @returns {number} Days 28-31
     */
    getDaysInMonth() {
        // day=0 of next month = last day of current month
        return new Date(Date.UTC(this.getYear(), this.getMonth(), 0)).getUTCDate();
    }

    /**
     * Get Unix timestamp in milliseconds
     * @returns {number}
     */
    getTimestamp() { return this.__date.getTime(); }


    getWeekNumber() {
        const date = new Date(this.__date);

        // Convert Sunday=0 → 6, Monday=1 → 0
        const day = (date.getUTCDay() + 6) % 7;

        // Shift to Thursday of current week (ISO anchor)
        date.setUTCDate(date.getUTCDate() - day + 3);

        // First Thursday of ISO year (Jan 4th is always in week 1)
        const firstThursday = new Date(Date.UTC(date.getUTCFullYear(), 0, 4));

        const firstDay = (firstThursday.getUTCDay() + 6) % 7;

        firstThursday.setUTCDate(firstThursday.getUTCDate() - firstDay + 3);

        // Calculate week number
        return 1 + Math.round(
            (date - firstThursday) / (7 * 24 * 60 * 60 * 1000)
        );
    }

    // number of week of the month the date currently in
    getWeeksInMonth(startDay) {
        const firstDayOfMonth = this.startOfMonth();

        // Normalize day index based on startDay
        const firstDayIndex = (firstDayOfMonth.getDayOfWeek() - startDay + 7) % 7;

        const totalDays = this.getDaysInMonth();

        // Total cells needed in calendar grid
        const totalCells = firstDayIndex + totalDays;

        // Number of rows (weeks)
        return Math.ceil(totalCells / 7);
    }
    // -------------------------
    // Formatters
    // -------------------------

    /**
     * Format as ISO date string (YYYY-MM-DD)
     * Safe for Carbon::parse() and ISO comparisons
     * @returns {string}
     */
    toISODateString() {
        return `${this.getYear()}-${String(this.getMonth()).padStart(2, '0')}-${String(this.getDay()).padStart(2, '0')}`;
    }

    /**
     * Format as full ISO string (YYYY-MM-DDTHH:mm:ss.sssZ)
     * Full UTC ISO usable for backend
     * @returns {string}
     */
    toISOString() {
        return this.__date.toISOString();
    }

    /**
     * Format using Intl.DateTimeFormat
     * @param {string} locale - BCP 47 language tag
     * @param {Object} [options={}] - Intl.DateTimeFormat options
     * @returns {string}
     */
    toFormattedString(locale, options = {}) {
        return new Intl.DateTimeFormat(locale, { timeZone: 'UTC', ...options }).format(this.__date);
    }

    /**
     * Get native UTC Date object
     * @returns {Date}
     */
    toDate() {
        return this.__date;
    }

    /**
     * Set month (1-12) and return new DateValue, clamping day to valid days in that month
     * @param {number} month - Month 1-12
     * @returns {DateValue} New date with updated month
     */
    setMonth(month) {
        const maxDay = new Date(Date.UTC(this.getYear(), month, 0)).getUTCDate();
        const newDay = Math.min(this.getDay(), maxDay);
        return new DateValue(this.getYear(), month, newDay);
    }

    /**
     * Set year and return new DateValue, clamping day to valid days in the same month of the new year
     * @param {number} year - Four-digit year
     * @returns {DateValue} New date with updated year
     */
    setYear(year) {
        const maxDay = new Date(Date.UTC(year, this.getMonth(), 0)).getUTCDate();
        const newDay = Math.min(this.getDay(), maxDay);
        return new DateValue(year, this.getMonth(), newDay);
    }

    /**
   * Check if a date string is valid
   * @static
   * @param {string} dateStr - Any date string
   * @returns {boolean}
   */
    isValid() {
        return this.__date.toString() !== DateValue.INVALID_DATE_STRING;
    }

    /**
     * Get today's date
     * @static
     * @returns {DateValue}
     */
    static today() {
        const now = new Date();
        return new DateValue(now.getUTCFullYear(), now.getUTCMonth() + 1, now.getUTCDate());
    }

    /**
     * Create DateValue from ISO string (YYYY-MM-DD or full ISO 8601)
     * @static
     * @param {string|null} isoString - ISO date string
     * @returns {DateValue|null}
     */
    static fromISOString(isoString) {
        if (!isoString || typeof isoString !== 'string') return null;

        const [year, month, day] = isoString.split('T')[0].split('-').map(Number);
        return new DateValue(year, month, day);
    }

    /**
     * Create DateValue from native UTC Date object
     * @static
     * @param {Date} date - UTC Date
     * @returns {DateValue}
     */
    static fromUTCDate(date) {
        return new DateValue(date.getUTCFullYear(), date.getUTCMonth() + 1, date.getUTCDate());
    }

    /**
     * Create DateValue from date parts [year, month, day]
     * @static
     * @param {number} year - Year
     * @param {number} month - Month (1-based)
     * @param {number} [day=1] - Day of month
     * @returns {DateValue}
     */
    static fromParts(year, month, day = 1) {
        return new DateValue(year, month, day);
    }

    /**
    * Format as ISO date string (YYYY-MM-DD)
    * Safe for Carbon::parse() and ISO comparisons
    * @returns {string}
    */
    static toISODateString(fullIsoString) {
        return DateValue.fromISOString(fullIsoString)?.toISODateString();
    }

    /**
     * Check if a date string is valid
     * @static
     * @param {string} dateStr - Any date string
     * @returns {boolean}
     */
    static isValid(dateStr) {
        return new Date(dateStr).toString() !== DateValue.INVALID_DATE_STRING;
    }

    /**
     * Create DateValue from UTC timestamp (milliseconds)
     *
     * @static
     * @param {number|null|undefined} ts - Unix timestamp in milliseconds
     * @returns {DateValue|null} DateValue instance or null if invalid input
     */
    static fromTimestamp(ts) {
        if (ts == null) return null;

        const d = new Date(ts);

        return new DateValue(d.getUTCFullYear(), d.getUTCMonth() + 1, d.getUTCDate());
    }
}
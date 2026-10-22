/**
 * Represents a day in the current month
 */
const Day = class {

    /** @type {number} Day of month (1-31) */
    number;

    /** @type {string} Formatted label for accessibility/display (e.g., "Monday, January 15, 2026") */
    label;

    /** @type {DateValue} DateValue instance for this day */
    dateValue;

    /** @type {boolean} True if day is disabled (outside min/max range or unavailable) */
    isDisabled;

    /** @type {boolean} True if day is marked as unavailable (selectable but may have special styling) */
    isUnavailable;

    /** @type {boolean} True if this day is the first cell in its week row */
    isFirstInRow;

    /** @type {boolean} True if this day is the last cell in its week row */
    isLastInRow;

    /** @type {boolean} True if this is the first day of the month (1st) */
    isFirstInMonth;

    /** @type {boolean} True if this is the last day of the month */
    isLastInMonth;

    /** @type {number} Month number (1-12) */
    month;

    /** @type {number} Year value */
    year;

    /** @type {string[]} Array of special day keys this date belongs to (for styling/features) */
    keys;

    /** @type {string|null} Tooltip text for this day, if applicable */
    tooltip;

    /**
     * @param {Object} config - Day configuration
     * @param {number} config.number - Day of month (1-31)
     * @param {DateValue} config.dateValue - DateValue instance for this day  
     * @param {Date} [config.date] - Date object
     * @param {boolean} config.isDisabled - Whether day is disabled (outside min/max)
     * @param {boolean} config.isUnavailable - Whether day is unavailable
     * @param {boolean} config.isFirstInRow - Whether day is first in week
     * @param {boolean} config.isLastInRow - Whether day is last in week
     * @param {boolean} config.isFirstInMonth - Whether day is first in month
     * @param {boolean} config.isLastInMonth - Whether day is last in month
     * @param {number} config.month - Month number (1-12)
     * @param {number} config.year - Year
     */
    constructor({
        number,
        label,
        dateValue,
        isDisabled,
        isUnavailable,
        isFirstInRow,
        isLastInRow,
        isFirstInMonth,
        isLastInMonth,
        month,
        year,
        keys = [],
        tooltip = null
    }) {
        this.number = number;
        this.label = label;
        this.dateValue = dateValue;
        this.isDisabled = isDisabled;
        this.isUnavailable = isUnavailable;
        this.isFirstInRow = isFirstInRow;
        this.isLastInRow = isLastInRow;
        this.isFirstInMonth = isFirstInMonth;
        this.isLastInMonth = isLastInMonth;
        this.month = month;
        this.year = year;
        this.keys = keys;
        this.tooltip = tooltip;
    }

    isBlank() {
        return false
    }
}

export default Day;
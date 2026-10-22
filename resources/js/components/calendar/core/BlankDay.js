/**
 * Represents a blank day from adjacent months (pre/post blanks in calendar view)
 * These are days from the previous/next month used to fill the calendar grid
 */
const BlankDay = class {

    /** @type {number} Day of month in the adjacent month */
    number;

    /** @type {string} Formatted label for accessibility/display */
    label;

    /** @type {DateValue} DateValue instance for this day */
    dateValue;

    /** @type {boolean} True if day is disabled (outside min/max range or unavailable) */
    isDisabled;

    /** @type {boolean} True if day is marked as unavailable */
    isUnavailable;

    /** @type {boolean} True if this blank is at a corner edge of the month boundary */
    inEdgeCorner;

    /** @type {boolean} True if this blank is at the middle edge of the month boundary */
    inEdgeMiddle;

    /** @type {number} Month number (1-12) of the adjacent month */
    month;

    /** @type {number} Year value of the adjacent month */
    year;

    /** @type {string|undefined} Type designation ('pre' for previous month, 'post' for next month) */
    type;

    /**
     * @param {Object} config - BlankDay configuration
     * @param {number} config.number - Day of month
     * @param {DateValue} config.dateValue - DateValue instance for this day  
     * @param {boolean} config.isDisabled - Whether day is disabled (outside min/max)
     * @param {boolean} config.isUnavailable - Whether day is unavailable
     * @param {boolean} config.inEdgeCorner - Whether at corner of month edge
     * @param {boolean} config.inEdgeMiddle - Whether at middle of month edge
     * @param {number} config.month - Month number (1-12)
     * @param {number} config.year - Year
     * @param {string} [config.type] - Type designation
     */
    constructor({
        number,
        label,
        dateValue,
        isDisabled,
        isUnavailable,
        inEdgeCorner,
        inEdgeMiddle,
        month,
        year,
        type
    }) {
        this.number = number;
        this.label = label;
        this.dateValue = dateValue;
        this.isDisabled = isDisabled;
        this.isUnavailable = isUnavailable;
        this.inEdgeCorner = inEdgeCorner;
        this.inEdgeMiddle = inEdgeMiddle;
        this.month = month;
        this.year = year;
        this.type = type;
    }

    isBlank() {
        return true;
    }
}

export default BlankDay;
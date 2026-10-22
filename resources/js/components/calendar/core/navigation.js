/**
 * NavigationIndex
 *
 * Sorted lookup structure over the timestamps of all ENABLED days in the
 * rendered months. Keyboard navigation computes real target dates with
 * DateValue math and uses this index only to answer:
 *  - is this exact day enabled? (has)
 *  - what is the closest enabled day in a given direction? (nearestOnOrAfter/Before)
 *
 * Day-of-week correctness lives in the caller (Manager) – this class never
 * jumps by list offsets, so disabled days can't skew navigation.
 */
class NavigationIndex {
    /**
     * @param {number[]} values - Ordered list of UTC timestamps (ms) of enabled days
     */
    constructor(values) {
        /**
         * Ordered timestamps
         * @type {number[]}
         */
        this.list = values;

        /**
         * Set of timestamps for O(1) membership checks
         * @type {Set<number>}
         */
        this.set = new Set(values);
    }

    /**
     * Whether an exact day is enabled in the rendered window
     * @param {number} ts - UTC timestamp (ms)
     * @returns {boolean}
     */
    has(ts) {
        return this.set.has(ts);
    }

    /**
     * Smallest enabled timestamp >= ts (binary search)
     * @param {number} ts - UTC timestamp (ms)
     * @returns {number|null} Enabled timestamp or null if none after ts
     */
    nearestOnOrAfter(ts) {
        let lo = 0;
        let hi = this.list.length - 1;
        let found = null;

        while (lo <= hi) {
            const mid = (lo + hi) >> 1;

            if (this.list[mid] >= ts) {
                found = this.list[mid];
                hi = mid - 1;
            } else {
                lo = mid + 1;
            }
        }

        return found;
    }

    /**
     * Largest enabled timestamp <= ts (binary search)
     * @param {number} ts - UTC timestamp (ms)
     * @returns {number|null} Enabled timestamp or null if none before ts
     */
    nearestOnOrBefore(ts) {
        let lo = 0;
        let hi = this.list.length - 1;
        let found = null;

        while (lo <= hi) {
            const mid = (lo + hi) >> 1;

            if (this.list[mid] <= ts) {
                found = this.list[mid];
                lo = mid + 1;
            } else {
                hi = mid - 1;
            }
        }

        return found;
    }
}

export default NavigationIndex;

/**
 * SheafUI Calendar Component
 * Author: Mohamed Charrafi
 * Project: SheafUI (https://sheafui.dev)
**/

import CalendarSelection from './core/CalendarSelection';
import Manager from './core/Manager';
import { $initState } from './core/entangle';
import { buildYears, monthsShort, resolveStartDay, weekdaysShort } from './core/utils';
import DateValue from './core/DateValue';
import attachInputMask from './core/masker';
import NavigationIndex from './core/navigation';
import PresetsManager from './core/presetsManager';

// ----------------------------------------------------------------------
// WHY THIS COMPONENT IS BUILT THIS WAY (static utilities, manual renders)
// ----------------------------------------------------------------------
// Alpine.js was designed for small UI sprinkles, not large state machines.
// A full calendar needs precise date math, range selection, keyboard nav,
// and complex interaction state. Doing it "the Alpine way" (everything
// reactive, templates with x-if/x-for) becomes unmaintainable and slow.
//
// Instead, I use (and I think it's the cleanest way actually):
//  - Plain JS classes (DateValue, Month, Manager, CalendarSelection)
//  - Static utilities to avoid Alpine Proxy issues (private fields break)
//  - Alpine.effect only for the one thing that must react: baseDate changes
//  - Manual rendering (render) instead of Alpine's automatic DOM updates
//
// This keeps the component predictable, debuggable, and fast.
// All logic is in one place – no black boxes.
// ----------------------------------------------------------------------

/**
 * Alpine calendar component with support for single, multiple, and range selection modes
 * Includes keyboard navigation, date constraints (min/max), and unavailable dates
 * @param {Object} [options] Configuration options
 * @param {Object|null} [options.livewire] - Livewire instance for binding
 * @param {string} [options.model] - Livewire model property name for binding
 * @param {boolean} [options.isLive=false] - Use live Livewire binding
 * @param {string} [options.mode='single'] - Selection mode: 'single', 'multiple', or 'range'
 * @param {boolean} [options.readOnly=false] - Prevent date selection
 * @param {boolean} [options.allowNavigation=true] - Allow month navigation
 * @param {string[]} [options.unavailableDates=[]] - Array of unavailable dates as ISO strings
 * @param {string|null} [options.min=null] - Minimum selectable date as ISO string
 * @param {string|null} [options.max=null] - Maximum selectable date as ISO string
 * @param {number|null} [options.minRange=null] - Minimum range length in days (range mode only)
 * @param {number|null} [options.maxRange=null] - Maximum range length in days (range mode only)
 * @param {number|'auto'} [options.startDay='auto'] - First day of week (0=Sunday, or 'auto' for locale)
 * @param {boolean} [options.fixedWeeks] - Use fixed 6-week calendar grid
 * @param {number} [options.numberOfMonths=1] - Number of months to display
 * @param {string} [options.locale='auto'] - BCP 47 language tag (or 'auto' for browser locale)
 * @param {boolean} [options.selectableMonths] - Show month dropdown
 * @param {boolean} [options.selectableYears] - Show year dropdown
 * @param {number[]} [options.yearsRange] - [start, end] for year dropdown (supports relative offsets)
 * @param {string} [options.openTo] - ISO date to initially open to (if no selection)
 * @param {boolean} [options.forceOpenTo] - Always open to openTo, even if selection exists
 * @param {Object} [options.specialDays] - { key: ['YYYY-MM-DD'] } for styling/tooltips
 * @param {string[]} [options.specialDisabled] - Keys from specialDays that should be disabled
 * @param {Object} [options.specialTooltips] - { key: 'tooltip text' }
 * @param {string} [options.separator='-'] - Separator for input mask display (internal uses '-')
 * @param {boolean} [options.withInputs] - Show bound text inputs for date entry
 * @returns {Object} Alpine.js data object with calendar methods and state
 */
const calendar = ({
    livewire,
    model,
    isLive,
    mode = 'single',
    readOnly = false,
    allowNavigation = true,
    unavailableDates = [],
    min = null,
    max = null,
    minRange = null,
    maxRange = null,
    startDay,
    fixedWeeks,
    numberOfMonths = 1,
    locale = 'auto',
    selectableMonths,
    selectableYears,
    yearsRange,
    openTo,
    forceOpenTo,
    specialDays,
    specialDisabled,
    specialTooltips,
    separator,
    withInputs,
}) => {

    const DATE_FORMAT = "yyyy-mm-dd";

    const resolvedLocale = locale === 'auto'
        ? (typeof navigator !== 'undefined' ? navigator.language : 'en-US')
        : locale;

    const resolvedStartDay = startDay === 'auto'
        ? resolveStartDay(resolvedLocale)
        : startDay;

    // Memoization for selection cache – avoids rebuilding the cache object
    // on every access unless __state actually changed.
    let cacheKey = null;
    let cachedValue = null;

    return {
        // ----- Reactive state (Alpine tracks these) -----
        __state: $initState(livewire, model, isLive),
        __locale: resolvedLocale,
        __startDay: resolvedStartDay,
        __numberOfRenderedMonths: numberOfMonths,
        __minDate: min ? DateValue.fromISOString(min) : null,
        __maxDate: max ? DateValue.fromISOString(max) : null,
        __baseDate: null,           // The month/year that determines which months are shown
        __focusedDay: null,         // Currently focused day object (for keyboard nav)
        __hoveredDayTs: null,       // Timestamp of day being hovered (range preview)
        __daysLabels: [],           // Weekday names (short)
        __renderedMonths: [],       // Array of month objects (output of Manager.render)

        // For month/year dropdowns
        __monthOptions: [],
        __yearOptions: [],

        // Cleanup utilities
        __abort_controller: new AbortController(),
        __inputMaskCleanup: null,

        // keyboard index (holding the rendered months index)
        __navigationIndex: null,

        __presetsManager: undefined,

        __DATE_FORMAT: DATE_FORMAT,
        // ----- SELECTION CACHE (optimized lookups) -----
        // Computed getter that rebuilds the cache only when __state changes.
        // Used by isSelectedDay, isRangeMiddle, etc. – avoids repeated
        // parsing of ISO strings and complex loops.
        get __cache() {
            const key = JSON.stringify(this.__state);
            if (cacheKey !== key) {
                cacheKey = key;
                cachedValue = CalendarSelection.buildCache(this.__state, mode);
            }
            return cachedValue;
        },

        // ----- LIFECYCLE -----
        init() {
            this.__daysLabels = weekdaysShort(this.__locale, this.__startDay);

            // Determine which month to show first based on selection, openTo, etc.
            this.__baseDate = CalendarSelection.generateBaseState(mode, this.__state, openTo, forceOpenTo);

            // THE SINGLE SOURCE OF REACTIVITY:
            // Whenever __baseDate changes (navigation, selection that changes month, etc.),
            // re-render the month grid. This is the only automatic reactivity get used in.
            Alpine.effect(() => this.__baseDate && this.render());

            this.$nextTick(() => {
                this.handleKeyDown();

                if (withInputs) {
                    // those are scoped to the calendar scope only
                    this.bindInputs({
                        inputEl: this.$refs.date_input,

                        // range mode
                        inputStartEl: this.$refs.date_input_start,
                        inputEndEl: this.$refs.date_input_end,
                    });
                }

                if (!model && this.$root?._x_model) {
                    this.__state = this.$root._x_model?.get()
                }
            });

            this.handleMonthAndYearSelect();

            // when the env is purely alpine month bouded to a livewire property
            if (!model) {
                this.$watch('__state', (val) => this.$root?._x_model?.set(val));
            }
        },

        buildNavigationIndex() {
            queueMicrotask(() => {
                const values = [];

                this.__renderedMonths.forEach(month => {
                    month.days.forEach(day => {
                        if (!day.isDisabled) {
                            values.push(day.dateValue.getTimestamp());
                        }
                    });
                });

                this.__navigationIndex = new NavigationIndex(values);
            });
        },

        setState(val) {
            this.__state = val;
        },
        getState() {
            return this.__state;
        },
        setBaseDate(val) {
            // we need always to defer the base state mutation until state get change correctly, 
            // so rendering goes smooth, and without it we can see broken layouts tokens sometimes
            queueMicrotask(() => {
                this.__baseDate = val;
            })
        },

        // meant to be used in multiple mode on pillbox variant 
        __removeDate(date) {
            if (mode != 'multiple') return;

            this.__state = this.__state.filter((dateItem) => dateItem !== date);
        },

        __reset() {
            if (mode === 'single') {
                this.__state = null;
            } else if (mode === 'multiple') {
                this.__state = [];
            } else if (mode === 'range') {
                this.__state = { start: null, end: null };
            }
        },

        // ----- RENDERING ENGINE -----
        render() {
            // Build month objects (preBlanks, days, postBlanks) via Manager
            this.__renderedMonths = Manager.render({
                baseDate: this.__baseDate,
                numberOfMonths: this.__numberOfRenderedMonths,
                unavailableDates,
                locale: resolvedLocale,
                fixedWeeks,
                startDay: this.__startDay,
                max,
                min,
                specialDays,
                specialDisabled,
                specialTooltips,
            });

            this.$nextTick(() => {
                // refresh the navigation index after each render
                !this.__renderedMonths || this.buildNavigationIndex();
            })

            // Special case for range mode with partial selection (start only):
            // We must re-apply minRange/maxRange constraints because navigation
            // regenerates months from scratch, wiping the previous constraints.
            if (mode === 'range') {
                const cache = this.__cache;

                if (cache?.type === 'range' && cache.start && !cache.end) {
                    this.__renderedMonths = CalendarSelection.applyRangeConstraintsToMonths(this.__renderedMonths, cache.start, minRange, maxRange);
                }
            }
        },

        // ----- NAVIGATION CONSTRAINTS (min,max and range mode with maxRange) -----
        // When the user has selected a range start but not yet an end,
        // and maxRange is set, we must prevent navigation to months that are
        // entirely outside the reachable window from that start date.
        // This getter builds the options object for Manager.canNavigateTo.
        get navigateToArgs() {
            let firstSelected = null;
            if (mode === 'range') {
                const cache = this.__cache;
                if (cache?.type === 'range' && cache.start && !cache.end) {
                    firstSelected = cache.start;
                }
            }

            let options = {
                numberOfMonths: this.__numberOfRenderedMonths,
                minDate: this.__minDate,
                maxDate: this.__maxDate,
            };

            if (firstSelected && maxRange) {
                options.rangeOptions = {
                    firstSelected,
                    maxRange,
                };
            }
            return options;
        },

        get __canJumpPrev() {
            if (!allowNavigation) return false;
            return Manager.canNavigateTo(Manager.prevMonth(this.__baseDate), this.navigateToArgs);
        },

        get __canJumpNext() {
            if (!allowNavigation) return false;
            return Manager.canNavigateTo(Manager.nextMonth(this.__baseDate), this.navigateToArgs);
        },

        // Used by month/year dropdowns to respect min/max dates
        selectableMonth(option) {
            return CalendarSelection.isMonthSelectable(this.__baseDate.getYear(), option.value, this.__minDate, this.__maxDate);
        },
        selectableYear(year) {
            return CalendarSelection.isYearSelectable(year, this.__minDate, this.__maxDate);
        },

        selectToday() {
            this.goToToday();
            // queueMicrotask ensures that __state update happens after the current
            // tick, avoiding potential race conditions with the render cycle.
            queueMicrotask(() => {
                this.__state = CalendarSelection.selectToday(mode);
            });
        },

        // Accessible label for screen readers (announces current month/year)
        get __srLabel() {
            return new Intl.DateTimeFormat(resolvedLocale, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(this.__baseDate?.toDate());
        },

        // ----- KEYBOARD NAVIGATION -----
        handleKeyDown() {
            if (!allowNavigation) return;
            if (!this.$refs.container) return;

            this.$refs.container.addEventListener('keydown', (e) => {
                if (!this.__focusedDay) return;
                if (!allowNavigation || readOnly) return;

                const delegate = (handler) => {
                    e.preventDefault();

                    if (!this.__navigationIndex) return;

                    const { key, baseDate } = handler();

                    // A move that slides the visible window obeys the same
                    // constraints as the prev/next buttons (min/max, maxRange).
                    if (baseDate !== this.__baseDate && !Manager.canNavigateTo(baseDate, this.navigateToArgs)) {
                        return;
                    }

                    this.__baseDate = baseDate;
                    // Focus the new day after the calendar re-renders
                    queueMicrotask(() => this.focusElWithKey(key));
                };

                const keyHandlers = {
                    'ArrowDown': () => Manager.nextDayInNextWeek(this.__focusedDay, this.__baseDate, this.__navigationIndex, numberOfMonths),
                    'ArrowUp': () => Manager.previousDayInPreviousWeek(this.__focusedDay, this.__baseDate, this.__navigationIndex),
                    'ArrowLeft': () => Manager.previousDay(this.__focusedDay, this.__baseDate, this.__navigationIndex),
                    'ArrowRight': () => Manager.nextDay(this.__focusedDay, this.__baseDate, this.__navigationIndex, numberOfMonths),
                    // APG date-grid keys
                    'Home': () => Manager.startOfWeek(this.__focusedDay, this.__baseDate, this.__navigationIndex, this.__startDay),
                    'End': () => Manager.endOfWeek(this.__focusedDay, this.__baseDate, this.__navigationIndex, this.__startDay),
                    'PageUp': () => Manager.sameDayPreviousMonth(this.__focusedDay, this.__baseDate, this.__navigationIndex),
                    'PageDown': () => Manager.sameDayNextMonth(this.__focusedDay, this.__baseDate, this.__navigationIndex, numberOfMonths),
                };

                const handler = keyHandlers[e.key];
                if (handler) delegate(handler);
            });
        },

        // ----- INPUT MASK BINDING -----
        // Used both by the calendar's own top-inputs AND by the date picker wrapper.
        // Pass refs directly so this works regardless of which component owns the DOM.
        bindInputs({ inputEl, inputStartEl, inputEndEl }) {

            const inputs = mode === 'range'
                ? [inputStartEl, inputEndEl]
                : [inputEl];

            inputs.forEach((input) => {

                if (!input) return;
                this.attachInputMask({
                    input,
                    onChange: (newValue) => {
                        if (mode === 'single') {
                            this.__state = newValue;
                        } else if (mode === 'range') {
                            this.handleRangeInputsBindings(input, newValue);
                        }
                    },
                });
            });

            // Sync inputs back when state changes externally (day click, preset, etc.)
            Alpine.effect(() => {
                if (mode === 'single') {
                    if (inputEl) {
                        inputEl.value = (DateValue.toISODateString(this.__state) || this.__DATE_FORMAT).replaceAll('-', separator);
                    }
                    return;
                }
                if (mode === 'range') {
                    if (inputStartEl) {
                        inputStartEl.value = (DateValue.toISODateString(this.__state?.start) ?? this.__DATE_FORMAT).replaceAll('-', separator);
                    }

                    if (inputEndEl) {
                        inputEndEl.value = (DateValue.toISODateString(this.__state?.end) ?? this.__DATE_FORMAT).replaceAll('-', separator);
                    }
                }
            });
        },


        attachInputMask({ input, onChange = () => { } }) {

            let cleanup = attachInputMask({
                input,
                abortSignal: this.__abort_controller.signal,
                onChange,
                separator
            });

            this.__inputMaskCleanup ??= [];
            this.__inputMaskCleanup.push(cleanup);
        },

        handleRangeInputsBindings(input, newValue) {
            let isStartInput = input.dataset.hasOwnProperty('start');
            let isEndInput = input.dataset.hasOwnProperty('end');

            if (isEndInput) {
                this.__state = { ...(this.__state ?? {}), end: newValue };
            }

            if (isStartInput) {
                this.__state = { ...(this.__state ?? {}), start: newValue };
            }
        },
        // Focus an element by its data-key attribute after re-render
        focusElWithKey(key) {
            const calendarContainer = this.$refs.container;
            if (!calendarContainer) return;
            const el = calendarContainer.querySelector(`[data-key="${key}"]:not([aria-disabled="true"])`);
            // Double requestAnimationFrame ensures the DOM has fully painted
            requestAnimationFrame(() => requestAnimationFrame(() => el?.focus()));
        },

        focusDay(day, index) {
            this.__focusedDay = { ...day, index };
        },

        // Used by the "today" button to display the current day number
        get __todayDay() {
            return DateValue.today().getDay();
        },

        // ----- NAVIGATION ACTIONS -----
        previousMonth() {
            if (!allowNavigation) return;
            const candidate = Manager.prevMonth(this.__baseDate);

            if (Manager.canNavigateTo(candidate, this.navigateToArgs)) {
                this.setBaseDate(candidate);
            }
        },
        nextMonth() {
            if (!allowNavigation) return;
            const candidate = Manager.nextMonth(this.__baseDate);

            if (Manager.canNavigateTo(candidate, this.navigateToArgs)) {
                this.setBaseDate(candidate);
            }
        },
        goToToday() {
            if (!allowNavigation) return;
            this.setBaseDate(Manager.goToToday());
        },

        // ----- DAY CLICK HANDLING -----
        dayClicked(day, index) {
            if (readOnly) return;
            if (day.isDisabled) return;

            this.__focusedDay = { ...day, index };

            let selector = CalendarSelection.select(day, this.__state, mode);
            this.__state = selector.state;

            if (mode === "range") {
                this.applyRangeLimits(selector);
            }
        },

        // Applies range constraints (minRange/maxRange) after selection,
        // and optionally re-renders if the range was completed.
        applyRangeLimits(selector) {
            if (typeof selector.applyRangeConstraints === 'function') {
                queueMicrotask(() => {
                    this.__renderedMonths = selector.applyRangeConstraints(
                        this.__renderedMonths,
                        minRange,
                        maxRange
                    );
                });
            }
            if (selector.shouldResetCalendar) {
                queueMicrotask(() => {
                    this.render();
                });
            }
        },

        // ----- SELECTION & RANGE HELPERS (delegate to CalendarSelection) -----
        isSelectedDay(day) {
            return CalendarSelection.isSelected(day.dateValue.toISODateString(), this.__cache);
        },
        isRangeMiddle(day) {
            return CalendarSelection.isBetween(day, this.__cache);
        },
        isHoverPreview(day) {
            if (mode !== 'range' || !this.__hoveredDayTs) return false;
            return CalendarSelection.isHoverPreview(day, this.__cache, this.__hoveredDayTs);
        },
        isHoverRangeEnd(day) {
            if (day.isDisabled) return;
            return CalendarSelection.isHoverRangeEnd(day.dateValue.getTimestamp(), this.__cache, this.__hoveredDayTs);
        },

        // ----- HOVER (for range preview) -----
        onDayHover(day) {
            if (readOnly) return;
            if (mode !== 'range') return;

            const cache = this.__cache;
            if (!cache || cache.end) return;
            if (day.isDisabled) {
                this.__hoveredDayTs = null;
                return;
            }
            this.__hoveredDayTs = day.dateValue.getTimestamp();
        },

        onDayHoverLeave() {
            if (mode !== 'range') return;
            this.__hoveredDayTs = null;
        },

        // Focus tracking for keyboard nav
        isFocusedDate(day) {
            if (!this.__focusedDay) return false;
            return this.__focusedDay.dateValue.getTimestamp() === day.dateValue.getTimestamp();
        },

        // Cleanup on component destruction
        destroy() {
            this.__inputMaskCleanup?.forEach(cleanup => cleanup());
            this.__abort_controller?.abort();
        },

        // ----- MONTH / YEAR DROPDOWNS -----
        handleMonthAndYearSelect() {
            if (selectableMonths) {
                const monthNames = monthsShort(this.__locale);
                this.__monthOptions = monthNames.map((label, idx) => ({
                    value: idx + 1,
                    label: label
                }));

                if (this.__minDate?.isValid() || this.__maxDate?.isValid()) {
                    this.__monthOptions = this.__monthOptions.filter(this.selectableMonth.bind(this));
                }
            }

            if (selectableYears) {
                this.__yearOptions = buildYears(yearsRange, this.__baseDate.getYear());

                if (this.__minDate?.isValid() || this.__maxDate?.isValid()) {
                    this.__yearOptions = this.__yearOptions.filter(this.selectableYear.bind(this));
                }
            }
        },
        setMonth(monthNumber) {
            this.setBaseDate(this.__baseDate.setMonth(monthNumber));
        },
        setYear(yearNumber) {
            this.setBaseDate(this.__baseDate.setYear(yearNumber));
        },

        // Computed getters/setters for x-model binding on month/year selects
        get __currentMonth() {
            return this.__baseDate.getMonth();
        },
        set __currentMonth(month) {
            this.setMonth(month);
        },
        get __currentYear() {
            return this.__baseDate.getYear();
        },
        set __currentYear(year) {
            this.setYear(year);
        },
        // Presets
        selectPresetWithKey(key) {
            if (readOnly) return;

            if (!PresetsManager.isValid(key)) {
                console.warn(`Invalid preset: ${key}`);
                return false;
            }


            // 'custom' generates no range – the user picks dates manually.
            // Returning true lets the date picker mark it as the active preset
            // (__activePreset lives on the date picker scope, not the calendar).
            if (key === 'custom') {
                return true;
            }

            const range = PresetsManager.getRange(key, mode);


            if (!range) {
                console.warn(`Could not generate range for preset: ${key}`);
                return false;
            }

            if (mode === 'single') {
                this.__state = range;

                const targetDate = DateValue.fromISOString(range);

                queueMicrotask(() => {
                    this.setBaseDate(targetDate);
                });
            } else {
                this.__state = { start: range.start, end: range.end, preset: key };

                const targetDate = range.start
                    ? DateValue.fromISOString(range.start)
                    : DateValue.today();

                this.setBaseDate(targetDate);
            }

            return true;
        },

        presets(activated, all) {
            this.__presetsManager = new PresetsManager(all);
        },

        // mode helpers 
        isMultipleMode() {
            return mode === 'multiple';
        },
        isSingleMode() {
            return mode === 'single';
        },
        isRangeMode() {
            return mode === 'range';
        },
    };
};

Alpine.data('calendarComponent', calendar);
export default calendar;
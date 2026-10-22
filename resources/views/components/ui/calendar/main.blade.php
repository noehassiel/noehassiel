@aware([
    'allowNavigation' => true,
    'selectable' => false,
    'readonly' => false, 
    'mode' => 'single',
    'numberOfMonths' => 1,
    'size' => 'md',
    'withToday' => false,
    'selectableMonths'=> false,
    'selectableYears'=> false,
    'locale' => 'auto',
    'startDay' => 'auto',
    'years-range' => [-10, +10],
    'openTo' => null,
    'forceOpenTo' => false,
    'specialTooltips' => [],
    'weekNumbers' => false
])
@php
     // SIZES STYLES LOGIC
    $cellSize = match($size){
        'xs' => '2rem',
        'sm' => '2.3rem',
        'md' => '2.6rem',
        'lg' => '2.9rem',
        'xl' => '3.2rem',
        '2xl' => '3.5rem',
    };

    $containerClasses = [
        '[--calendar-width:calc(var(--cell-size)_*_7_*_var(--months)_+_2rem)]', 
        '[:where(&)]:max-w-(--calendar-width)',
        '[:where(&)]:min-w-(--calendar-width)', 
        'rounded-box w-full'
    ];
@endphp

<main 
    style="
        --months: {{ $numberOfMonths }};
        --cell-size: {{ $cellSize }};
    "
    class="flex items-start"
    {{ $attributes->class($containerClasses) }} 
    x-ref="container"
>
    <template x-for="(month, index) in __renderedMonths" x-bind:key="index">
        <div class="px-1 flex-shrink-0">
            <div
                class="sr-only"
                aria-live="polite"
                x-text="__srLabel"
            ></div>
            <header class="flex items-center flex-1 p-2">
                @if($allowNavigation)
                    <template x-if="index === 0">
                        <button 
                            x-bind:data-cant-jump="!__canJumpPrev"
                            x-on:click="previousMonth()"
                            type="button"
                            aria-label="Previous month"
                            class="p-1.5 cursor-pointer data-cant-jump:cursor-not-allowed data-cant-jump:opacity-50 rounded-field [&:not([data-cant-jump])]:hover:bg-neutral-100 dark:[&:not([data-cant-jump])]:hover:bg-white/5 transition-colors"
                        >
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                    </template>
                @endif

                <div 
                    class="flex-1 flex items-center justify-center text-center" 
                    x-data="{ selectableMonths: @js((bool) $selectableMonths), selectableYears: @js((bool) $selectableYears) }"
                >
                    {{--
                        Static month label for the first month (index === 0) only when month selector is disabled.
                        This ensures the natural "Month Year" order: static month appears before the year select if only years are selectable.
                    --}}
                    <span x-text="month.label" x-show="index === 0 && !selectableMonths" class="font-semibold mr-1.5 "></span>

                    {{-- 
                        Selector controls (month and/or year) appear ONLY on the first rendered month.
                        This avoids duplicated controls when multiple months are shown.
                        They replace the static labels when their respective selection is enabled.
                    --}}
                    <template x-if="index === 0 && (selectableMonths || selectableYears)">
                        <div class="flex items-center justify-center gap-2" ignore>
                            {{-- Month dropdown – replaces static month label when selectableMonths is true --}}
                            <template x-if="selectableMonths">
                                <select
                                    x-model="__currentMonth"
                                    x-on:input.stop {{-- solves the value from this to be bounded to the state itself on select behavior--}}
                                    x-init="$nextTick(() => $el.value = __currentMonth)"
                                    class="h-10 py-0 border-0 text-sm sm:h-8 appearance-none rounded-lg bg-neutral-100 dark:bg-white/10 dark:[&>option]:bg-neutral-700 dark:[&>option]:text-white px-3 ring-transparent sm:ps-2 bg-position-[right_.25rem_center]  pe-[1.40rem]"
                                >
                                    <template x-for="option in __monthOptions" x-bind:key="option.value">
                                        <option x-text="option.label" x-bind:value="option.value"/>
                                    </template>
                                </select>
                            </template>
                            {{-- Year dropdown – replaces static year label when selectableYears is true --}}
                            <template x-if="selectableYears">
                                <select
                                    x-model="__currentYear"
                                    x-on:input.stop {{-- for the same issue as the select above--}}
                                    x-init="$nextTick(() => $el.value = __currentYear)"
                                    class="h-10 py-0 border-0 ring-transparent  text-sm sm:h-8 [-moz-appearance:none] dark:[color-scheme:dark] appearance-none rounded-lg bg-neutral-100 dark:bg-white/10  px-3 sm:ps-2 bg-position-[right_.25rem_center] pe-[1.40rem]"
                                >
                                    <template x-for="year in __yearOptions" x-bind:key="year">
                                        <option class="dark:bg-neutral-800! bg-neutral-100 rounded dark:text-white" x-text="year" x-bind:value="year"/>
                                    </template>
                                </select>
                            </template>
                        </div>
                    </template>

                    {{-- 
                        Static labels for all months (appear inside this div.contents container).
                        Month label:
                            - Shown on all months EXCEPT the first month when month selector is enabled.
                            - Guarantees that non‑first months always display a readable month name.
                        Year label:
                            - Shown on all months EXCEPT the first month when year selector is enabled.
                            - For first month with year selector disabled, the static year appears.
                        This layout always presents "Month Year" in that order, whether static or selectable.
                    --}}
                    <div class="contents">
                        <span 
                            x-text="month.label" 
                            x-show="index !== 0"  
                            class="font-semibold"
                        ></span>

                        <span 
                            x-text="month.year" 
                            x-show="!selectableYears || index !== 0" 
                            class="ml-1.5 text-neutral-600 dark:text-neutral-400"
                        ></span>
                    </div>
                </div>

                {{-- Today button – appears only on the last rendered month when withToday is true --}}
                <template x-if="index === __renderedMonths.length - 1 && @js($withToday)">
                    <button 
                        x-on:click="selectToday" 
                        class="hover:dark:bg-white/5 hover:bg-neutral-950/5 p-1.5 mx-1.5 rounded-md"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            <text 
                                x="12" 
                                y="19.25" 
                                text-anchor="middle" 
                                fill="currentColor"
                                stroke="none"
                                font-size="12"
                                font-weight="600"
                                x-text="__todayDay"
                            ></text>
                        </svg>
                    </button>
                </template>

                @if ($allowNavigation)
                    <template x-if="index === __renderedMonths.length - 1">
                        <button 
                            x-bind:data-cant-jump="!__canJumpNext"
                            x-on:click="nextMonth()"
                            type="button"
                            aria-label="Next month"
                            class="p-1.5 cursor-pointer data-cant-jump:cursor-not-allowed data-cant-jump:opacity-50 rounded-field [&:not([data-cant-jump])]:hover:bg-neutral-100 dark:[&:not([data-cant-jump])]:hover:bg-white/5 transition-colors"
                        >
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </template>
                @endif
            </header>
            
            {{-- DAYS GRID: grid → rows → gridcells (APG date-grid structure) --}}
            <div
                x-data="{ readonly: @js($readonly) }"
                role="grid"
                x-bind:aria-label="month.label + ' ' + month.year"
                @if($mode === 'multiple') aria-multiselectable="true" @endif
            >
                {{-- weekday labels row --}}
                <div role="row" class="grid @if($weekNumbers) grid-cols-8 @else grid-cols-7 @endif text-neutral-600 dark:text-neutral-400">
                    @if($weekNumbers)
                        <div role="columnheader" aria-label="Week number" class="flex items-center justify-center h-10">
                            <span class="text-xs font-medium">#</span>
                        </div>
                    @endif

                    <template x-for="(day, index) in __daysLabels" x-bind:key="index">
                        <div
                            role="columnheader"
                            class="flex items-center justify-center h-10"
                            x-bind:aria-label="day"
                        >
                            <span x-text="day" class="text-xs font-medium"></span>
                        </div>
                    </template>
                </div>

                {{-- week rows --}}
                <div class="space-y-1">
                    <template x-for="week in month.weeks" x-bind:key="week.days[0].dateValue.getTimestamp()">
                        <div role="row" class="grid @if($weekNumbers) grid-cols-8 @else grid-cols-7 @endif">
                            @if($weekNumbers)
                                <div role="rowheader" data-slot="calendar-week-num-cell" x-text="week.num"></div>
                            @endif

                            {{--
                                One <button> per cell for blanks AND real days: the range styling
                                in cell.css relies on adjacent-sibling selectors (`:has(+ ...)`),
                                so every cell in a row must stay a direct sibling – no wrappers,
                                no x-if branching. Blank vs day is expressed through bindings.
                            --}}
                            <template x-for="day in week.days" x-bind:key="day.dateValue.getTimestamp()">
                                <button
                                    type="button"
                                    role="gridcell"
                                    data-mode="{{ $mode }}"
                                    x-bind:data-slot="day.isBlank() ? 'calendar-blank-day-cell' : 'calendar-cell'"

                                    x-bind:tabindex="!day.isBlank() && isFocusedDate(day) ? 0 : -1"
                                    x-bind:aria-selected="day.isBlank() ? null : (isSelectedDay(day) ? 'true' : 'false')"
                                    x-bind:aria-disabled="day.isDisabled ? 'true' : 'false'"
                                    x-bind:aria-current="!day.isBlank() && day.dateValue.isToday() ? 'date' : null"
                                    x-bind:aria-label="day.label"

                                    {{-- data-key drives focusElWithKey – real days only, blanks may duplicate timestamps across months --}}
                                    x-bind:data-key="day.isBlank() ? null : day.dateValue.getTimestamp()"
                                    x-bind:data-selected="isSelectedDay(day)"
                                    x-bind:data-readonly="readonly"
                                    x-bind:data-today="!day.isBlank() && day.dateValue.isToday()"
                                    x-bind:data-disabled="day.isDisabled"
                                    x-bind:data-focused="!day.isBlank() && isFocusedDate(day)"
                                    x-bind:data-unavailable="day.isUnavailable"
                                    x-bind:data-range-middle="isRangeMiddle(day)"
                                    {{-- hover feature for range mode --}}
                                    x-bind:data-hover-preview="isHoverPreview(day)"
                                    x-bind:data-hover-end="isHoverRangeEnd(day)"
                                    {{-- hover managers --}}
                                    x-on:mouseenter="day.isBlank() || onDayHover(day)"
                                    x-on:mouseleave="onDayHoverLeave()"
                                    x-bind:data-first-in-row="!day.isBlank() && day.isFirstInRow"
                                    x-bind:data-last-in-row="!day.isBlank() && day.isLastInRow"
                                    {{-- month-boundary edges (blanks only) --}}
                                    x-bind:data-first-day-in-pre-blanks="day.type === 'pre' && day.inEdgeCorner"
                                    x-bind:data-last-day-in-pre-blanks="day.type === 'pre' && day.inEdgeMiddle"
                                    x-bind:data-first-day-in-post-blanks="day.type === 'post' && day.inEdgeMiddle"
                                    x-bind:data-last-day-in-post-blanks="day.type === 'post' && day.inEdgeCorner"

                                    x-on:click="dayClicked(day, index)"
                                    x-on:focus="day.isBlank() || focusDay(day, index)"
                                    x-on:keydown.enter.prevent="dayClicked(day, index)"
                                    x-on:keydown.space.prevent="dayClicked(day, index)"

                                    {{-- special days bindings (real days only) --}}
                                    x-bind:data-special="!day.isBlank() && day.keys.length ? day.keys.join(' ') : false"
                                    x-bind:data-has-tooltip="!day.isBlank() && day.tooltip?.length > 0"
                                >
                                    <span x-text="day.number"></span>

                                    <template x-if="!day.isBlank() && day.tooltip?.length > 0">
                                        <span x-text="day.tooltip" data-special-tooltip></span>
                                    </template>

                                    <template x-if="!day.isBlank() && day.dateValue.isToday()">
                                        <span data-today-point></span>
                                    </template>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</main>
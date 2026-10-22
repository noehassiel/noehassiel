
@props([
    'mode' => 'single',
    'max' => null,
    'min' => null,
    'maxRange' => null,
    'minRange' => null,
    'readonly' => false,
    'allowNavigation' => true,
    'fixedWeeks' => 0,
    'weekNumbers' => false,
    'size' => 'md',
    'months' => 1,
    'unavailableDates' => [],
    'selectableMonths' => false,
    'selectableYears' => false,
    'withToday' => false,
    'locale' => 'auto',
    'startDay' => 'auto',
    'yearsRange' => [-10, +10],
    'openTo' => null,
    'forceOpenTo' => false,
    'topInputs' => false,

    // special days props
    'specialDays' => [],
    'specialDisabled' => [],
    'specialTooltips' => [],
    'separator' => '-',
])

@php
    // Detect if the component is bound to a Livewire model
    $modelAttrs = collect($attributes->getAttributes())->keys()->first(fn($key) => str_starts_with($key, 'wire:model'));

    $model = $modelAttrs ? $attributes->get($modelAttrs) : null;

    // Detect if model binding uses `.live` modifier (for real-time syncing)
    $isLive = $modelAttrs && str_contains($modelAttrs, '.live');

    $livewireId = isset($__livewire) ? $__livewire->getId() : null;

    $isRangeMode = $mode === 'range';

    if ($topInputs && $mode === 'multiple') {
        throw new \InvalidArgumentException(
            '[Calendar] top-inputs is not supported in multiple mode. ' .
                'Multiple date selection is inherently click-based and does not map to a text input. ' .
                'Remove top-inputs or switch to mode="single" or mode="range".',
        );
    }
@endphp

<div 
    x-data="calendarComponent({
        // adapt component with livewire or pure alpinejs
        model: @js($model),
        livewire: @js(isset($livewireId)) ? window.Livewire.find(@js($livewireId)) : null,
        isLive: @js($isLive),

        // typical props
        mode: @js($mode),
        min: @js($min),
        max: @js($max),
        minRange: @js($minRange),
        maxRange: @js($maxRange),
        readOnly: @js($readonly),
        allowNavigation: @js($allowNavigation),
        unavailableDates: @js($unavailableDates),
        fixedWeeks: @js($fixedWeeks),
        numberOfMonths: @js($months),
        locale: @js($locale),
        selectableMonths: @js((bool)$selectableMonths),
        selectableYears: @js((bool)$selectableYears),
        startDay: @js((int) $startDay),
        yearsRange: @js($yearsRange),
        openTo: @js($openTo),
        forceOpenTo: @js($forceOpenTo),
        specialDays: @js($specialDays),
        specialDisabled: @js($specialDisabled),
        specialTooltips: @js($specialTooltips),
        separator: @js($separator),
        withInputs: @js($topInputs),
    })" 
    {{ $attributes->class('flex flex-col items-center gap-3 p-1') }}
    x-ref="calendar"
    data-slot="calendar"
    wire:ignore
>
    @if ($topInputs)
        {{-- we use `bindScopeToParent` to expose the $refs to this `_x_dataStack` level --}}
        @if ($isRangeMode)
            <div class="sm:px-2 flex items-center gap-4 font-medium text-neutral-700 dark:text-neutral-200">
                <div class="flex items-center gap-2.5">
                    <span class="text-sm">
                        Start:
                    </span>
                    <x-ui.input class="w-full sm:w-40" rightIcon="calendar" bindScopeToParent x-ref="date_input_start" :readonly="$readonly" data-start />
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-sm">
                        End:
                    </span>
                    <x-ui.input class="w-full sm:w-40" rightIcon="calendar" bindScopeToParent x-ref="date_input_end" :readonly="$readonly" data-end />
                </div>
            </div>
        @else
            <x-ui.input class="w-full sm:w-40" rightIcon="calendar" bindScopeToParent x-ref="date_input" :readonly="$readonly" />
        @endif
    @endif

    <x-ui.calendar.main :$attributes />
</div>

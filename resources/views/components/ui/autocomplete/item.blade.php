@props([
    'value' => null,
])

@php
    $value ??= $slot->__toString();
@endphp

<li 
    x-show="itemShouldShow(@js($value))"
    data-slot="autocomplete-item"
    
    x-on:mouseleave="$el.blur()"
    x-on:mouseenter="handleMouseEnter(@js($value))"
    
    x-bind:id="'option-' + getFilteredIndex(@js($value))"
    
    x-on:click.stop="select(@js($value))"

    x-bind:data-value="@js($value)"

    tabindex="0"

    x-bind:id="'item-' + getFilteredIndex(@js($value))"
    
    x-bind:class="{
        'bg-neutral-300 dark:bg-neutral-700 hover:bg-neutral-100 hover:dark:bg-neutral-700': isFocused(@js($value)),
        '[&>[data-slot=icon]]:opacity-100': isSelected(@js($value)),
    }"

    role="option"
    x-bind:aria-selected="isSelected(@js($value))"

    class="cursor-pointer focus:bg-neutral-100 focus:dark:bg-neutral-700 px-3 py-1 rounded-[calc(var(--popup-round)-var(--popup-padding))] w-full text-[1rem] text-neutral-950 dark:text-neutral-50"
>
    {{ $slot }}
</li>
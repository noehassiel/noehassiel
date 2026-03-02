
<x-ui.popup
    x-show="popupShouldShown" 
    x-anchor.offset.3="$refs.autocompleteControl" 
    x-on:click.away="handleClickAway($event)"  
    x-ref="dropdown"
    :autofocus="false"
>
    <ul 
        x-ref="autocompleteOptions" 
        class="flex flex-col gap-y-1"  
        role="listbox"              
    >
        {{ $slot }}
        <!-- no result shown fallback -->          
        <li 
            x-show="!hasVisibleItems" 
            class="px-3 py-2 text-center rounded-[calc(var(--popup-round)-var(--padding-popover))] text-neutral-500 dark:text-neutral-400"
        >
            No results found
        </li>
    </ul>
</x-ui.popup>
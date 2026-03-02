@props([
    'name' => $attributes->whereStartsWith('wire:model')->first() ?? $attributes->whereStartsWith('x-model')->first(),
    'type' => 'text',
    'placeholder' => 'Search...',
    'variant' => 'default',
    'disabled' => false,
    'readonly' => false,
    'invalid' => false,
    'leftIcon' => '',
    'rightIcon' => '',
    'clearable' => false,
    'inputClasses' => '',
    'input' => null
])

<div 
    x-data="{
        open: false,
        search: '',
        state: null,
        isTyping: false,
        readonly: @js($readonly),

        // responsible for (like) combobox pattern https://www.w3.org/WAI/ARIA/apg/patterns/combobox/ 
        // it keeps focus on the input while navigating results
        activeIndex: null,
        options: [],
        filteredOptions:[],

        select(item) {
            if(this.readonly) return;
            this.open = false;
            this.state = item;
            this.search = item;
            this.isTyping = false;
        },
        
        init() {
            this.$nextTick(() => {
                this.filteredOptions = this.options = Array
                    .from($el.querySelectorAll('[data-slot=autocomplete-item]:not([hidden])'))
                    .map((item) =>({
                        value: item.dataset.value,
                        element: item
                    }));
                
                // Get initial state from x-model or wire:model
                const modelValue = this.$root._x_model?.get();
                if (modelValue) {
                    this.state = modelValue;
                    this.search = modelValue;
                }
            })

            this.$watch('state', (value) => {
                // Sync with Alpine state
                this.$root?._x_model?.set(value);

                // Sync with Livewire state
                let wireModel = this?.$root.getAttributeNames().find(n => n.startsWith('wire:model'))

                if(this.$wire && wireModel){
                    let prop = this.$root.getAttribute(wireModel)
                    this.$wire.set(prop, value, wireModel?.includes('.live'));
                }
            });

            this.$watch('search', (val) => {
                // reset highlighted item whenever search text changes I don't like it, you may so here it is comented
                // this.activeIndex = null;

                if (val.trim() === '') {
                    // empty search → restore full option list 
                    // (important for accessibility keyboard navigation)
                    this.filteredOptions = this.options;
                } else {
                    // filter options by search query 
                    this.filteredOptions = this.options.filter(option =>
                        option.value.toLowerCase().includes(val.toLowerCase().trim())
                    );
                }
            })
        },

        handleInput() {
            this.isTyping = true;
            this.open = true;
        },
        
        handleFocus() {
            this.open = true;
            this.isTyping = false;
        },
        
        clear() {
            this.open = false;
            this.isTyping = false;
            this.state = null;
            this.search = '';
        },

        handleClickAway(e) {
            // Check if clicking on the input control, if so we don't need to close the dropdown
            if (
                e.target.hasAttribute('data-slot') &&
                e.target.getAttribute('data-slot') === 'control'
            ) {
                return; 
            }
            
            this.open = false;
        },
        
        itemShouldShow(item) {
            if (!this.isTyping || !this.search.trim()) {
                return true;
            }

            return this.contains(item, this.search)
        },
        
        get hasVisibleItems() {
            if (!this.isTyping || !this.search.trim()) {
                return this.options.length > 0;
            }

            return this.options.some(item => this.contains(item.value,this.search));
        },
        
        // A11y managment 
        handleKeydown(event) {
            if(event.key === 'ArrowDown') {
                if (this.activeIndex === null || this.activeIndex >= this.filteredOptions.length - 1) {
                    this.activeIndex = 0;
                } else {
                    this.activeIndex++;
                }
            }

            if(event.key === 'ArrowUp') {
                if (this.activeIndex === null || this.activeIndex <= 0) {
                    this.activeIndex = this.filteredOptions.length - 1;
                } else {
                    this.activeIndex--;
                }
            }

            if(event.key === 'Enter' && this.activeIndex !== null) {
                let item = this.filteredOptions[this.activeIndex];
                this.select(item.value);
            }
        },
        get popupShouldShown() {
            return this.open && this.options.length > 0;
        },
        
        isSelected(item) {
            return item === this.state;
        },
        // Get the filtered index for a given value
        getFilteredIndex(value) {
            return this.filteredOptions.findIndex(item => item.value === value);
        },
        
        // Handle mouse enter - find the index in filtered results
        handleMouseEnter(value) {
            this.activeIndex = this.getFilteredIndex(value);
        },
        
        // Check if item is focused based on its position in filtered results
        isFocused(value) {
            return this.activeIndex !== null && this.getFilteredIndex(value) === this.activeIndex;
        },
        
        get hasFilteredResults() {
            return this.filteredOptions.length > 0;
        },
        
        contains(str, substring){
            return str.toLowerCase().includes(substring.toLowerCase());
        }
    }"

    {{ 
        $attributes->class('relative text-start [--popup-round:var(--radius-box)] [--popup-padding:--spacing(1)]')
    }}
>

    <div>
        @if($input)    
            {{ $input }}
        @else
            <x-ui.input
                {{-- disable actual input scope and uses this autocomplete scope --}}
                :bindToParentScope="true"

                x-ref="autocompleteControl"  
                x-model="search"
                x-on:click="open = true"
                x-on:keydown.escape="open = false"
                x-on:keydown.up.prevent.stop="handleKeydown($event)"
                x-on:keydown.down.prevent.stop="handleKeydown($event)"
                x-on:keydown.enter.prevent.stop="handleKeydown($event)"
                x-bind:aria-activedescendant="activeIndex !== null ? 'option-' + activeIndex : null"
                x-on:focus="handleFocus()"
                x-on:input.stop="handleInput()"
                autocomplete="off"

                {{-- passing through manually --}}
                :$placeholder
                :id="$name"
                :$readonly
                :$disabled
                :$disabled
                :$clearable
                :$name
                :$type

                {{-- icons --}}
                :$rightIcon
                :$leftIcon
            />
        @endif
    </div>   
    <x-ui.autocomplete.items>
        {{ $slot }}
    </x-ui.autocomplete.items>
</div>
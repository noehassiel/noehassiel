@props([])

<kbd
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 rounded border border-neutral-200 bg-neutral-100 px-1 py-0.5 font-mono text-xs text-neutral-600 dark:border-white/10 dark:bg-white/5 dark:text-neutral-400']) }}
>{{ $slot }}</kbd>

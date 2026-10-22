{{-- Shared admin page header: back link, eyebrow, title, subtitle and actions --}}
@props([
    'title',
    'back' => null,
    'backLabel' => null,
    'eyebrow' => null,
    'subtitle' => null,
])

<header {{ $attributes->class('admin-page-header mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" wire:navigate class="admin-back">
                <x-ui.icon name="arrow-left" class="size-3.5" />
                {{ $backLabel }}
            </a>
        @elseif ($eyebrow)
            <p class="admin-eyebrow mb-2">{{ $eyebrow }}</p>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <h1 class="admin-title truncate">{{ $title }}</h1>
            {{ $meta ?? '' }}
        </div>

        @if ($subtitle)
            <p class="admin-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</header>

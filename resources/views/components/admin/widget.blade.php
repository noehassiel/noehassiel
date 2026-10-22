{{-- Card shell + inset panel, adapted from the Console general dashboard --}}
@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'tag' => null,
    'live' => false,
])

<section {{ $attributes->class('admin-widget') }}>
    <header class="flex items-center gap-3 px-3 pt-2.5 pb-3">
        @if ($icon)
            <span class="admin-widget-icon">
                <x-ui.icon :name="$icon" class="size-[18px]" />
            </span>
        @endif

        <div class="min-w-0 flex-1">
            <h2 class="truncate text-[15px] font-medium text-[var(--heading)]">{{ $title }}</h2>
            @if ($subtitle)
                <p class="truncate text-[12.5px] text-[var(--muted)]">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($live)
            <span class="admin-chip text-emerald-400">
                <span class="admin-live-dot"></span>{{ __('Live') }}
            </span>
        @elseif ($tag)
            <span class="admin-chip">{{ $tag }}</span>
        @endif

        {{ $tools ?? '' }}
    </header>

    <div class="admin-inset">
        {{ $slot }}
    </div>
</section>

@props(['key', 'html' => false])

{{-- Renders both languages; html[data-lang] decides which one is visible --}}
{{-- html: only for trusted lang strings that carry markup (e.g. links) --}}
@if ($html)
<span data-lang="en" {{ $attributes }}>{!! __('landing.'.$key, locale: 'en') !!}</span><span data-lang="es" {{ $attributes }}>{!! __('landing.'.$key, locale: 'es') !!}</span>
@else
<span data-lang="en" {{ $attributes }}>{{ __('landing.'.$key, locale: 'en') }}</span><span data-lang="es" {{ $attributes }}>{{ __('landing.'.$key, locale: 'es') }}</span>
@endif

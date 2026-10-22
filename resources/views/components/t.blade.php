@props(['key'])

{{-- Renders both languages; html[data-lang] decides which one is visible --}}
<span data-lang="en" {{ $attributes }}>{{ __('landing.'.$key, locale: 'en') }}</span><span data-lang="es" {{ $attributes }}>{{ __('landing.'.$key, locale: 'es') }}</span>

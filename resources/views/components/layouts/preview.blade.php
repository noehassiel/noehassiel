<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    {{-- Full-screen review shell: no portal chrome, the client's site takes the whole window --}}
    <body class="portal overflow-hidden">
        {{ $slot }}
    </body>
</html>

<aside>
    {{ $title }}

    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <x-ui.button as="button" variant="ghost" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
            {{ __('Log Out') }}
        </x-ui.button>
    </form>
</aside>

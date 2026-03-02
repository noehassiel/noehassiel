<aside>
    hello

    {{ $title }}

    <form method="POST" action="{{ route('logout') }}" class="w-full">
        @csrf
        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
            {{ __('Log Out') }}
        </flux:menu.item>
    </form>
</aside>

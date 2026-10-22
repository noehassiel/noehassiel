<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <x-ui.navlist>
            <x-ui.navlist.item :href="route('settings.profile')" label="{{ __('Profile') }}" wire:navigate />
            <x-ui.navlist.item :href="route('settings.security')" label="{{ __('Security') }}" wire:navigate />
            <x-ui.navlist.item :href="route('settings.appearance')" label="{{ __('Appearance') }}" wire:navigate />
        </x-ui.navlist>
    </div>

    <x-ui.separator vertical class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <x-ui.heading>{{ $heading ?? '' }}</x-ui.heading>
        <x-ui.description>{{ $subheading ?? '' }}</x-ui.description>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        @include('livewire.settings.partials.security-sections')
    </x-settings.layout>

    @include('livewire.settings.partials.security-modals')
</section>

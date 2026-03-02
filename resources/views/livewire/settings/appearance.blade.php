<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <x-ui.theme-switcher variant="inline" />
    </x-settings.layout>
</section>

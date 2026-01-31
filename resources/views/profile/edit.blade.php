<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-foreground leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="max-w-2xl mx-auto">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="max-w-2xl mx-auto">
                @include('profile.partials.update-password-form')
            </div>

            <div class="max-w-2xl mx-auto">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>

<section>
    <div class="border border-gray-200 rounded-xl shadow-2xs">
        <div class="p-4 sm:p-7">
            <header>
                <h2 class="text-2xl font-bold text-foreground">
                    {{ __('Update Password') }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground-2">
                    {{ __('Ensure your account is using a long, random password to stay secure.') }}
                </p>
            </header>

            <form method="post" action="{{ route('password.update') }}" class="mt-5">
                @csrf
                @method('put')

                <!-- Current Password -->
                <x-input-group>
                    <x-input-label for="update_password_current_password" label="Current Password" />
                    <x-text-input
                        id="update_password_current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                    />
                    @error('current_password', 'updatePassword')
                    <x-input-error :message="$message" />
                    @enderror
                </x-input-group>

                <!-- New Password -->
                <x-input-group>
                    <x-input-label for="update_password_password" label="New Password" />
                    <x-text-input
                        id="update_password_password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                    />
                    @error('password', 'updatePassword')
                    <x-input-error :message="$message" />
                    @enderror
                </x-input-group>

                <!-- Confirm Password -->
                <x-input-group>
                    <x-input-label for="update_password_password_confirmation" label="Confirm Password" />
                    <x-text-input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                    />
                    @error('password_confirmation', 'updatePassword')
                    <x-input-error :message="$message" />
                    @enderror
                </x-input-group>

                <!-- Submit Button and Success Message -->
                <x-input-group>
                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                            {{ __('Save') }}
                        </button>

                        @if (session('status') === 'password-updated')
                            <p class="text-sm text-green-600 font-medium">
                                {{ __('Saved.') }}
                            </p>
                        @endif
                    </div>
                </x-input-group>
            </form>
        </div>
    </div>
</section>

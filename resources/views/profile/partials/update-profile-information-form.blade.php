<section>
    <div class="border border-gray-200 rounded-xl shadow-2xs">
        <div class="p-4 sm:p-7">
            <header>
                <h2 class="text-2xl font-bold text-foreground">
                    {{ __('Profile Information') }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground-2">
                    {{ __("Update your account's profile information and email address.") }}
                </p>
            </header>

            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="mt-5">
                @csrf
                @method('patch')

                <!-- Name -->
                <x-input-group>
                    <x-input-label for="name" label="Name" />
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        :value="old('name', $user->name)"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    @error('name')
                    <x-input-error :message="$message" />
                    @enderror
                </x-input-group>

                <!-- Email -->
                <x-input-group>
                    <x-input-label for="email" label="Email" />
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        :value="old('email', $user->email)"
                        required
                        autocomplete="username"
                    />
                    @error('email')
                    <x-input-error :message="$message" />
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                {{ __('Your email address is unverified.') }}
                                <button
                                    form="send-verification"
                                    type="submit"
                                    class="underline text-sm text-yellow-900 hover:text-yellow-950 font-medium focus:outline-none">
                                    {{ __('Click here to re-send the verification email.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-sm text-green-600 font-medium">
                                    {{ __('A new verification link has been sent to your email address.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </x-input-group>

                <!-- Submit Button and Success Message -->
                <x-input-group>
                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                            {{ __('Save') }}
                        </button>

                        @if (session('status') === 'profile-updated')
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

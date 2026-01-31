<x-auth-layout>
    <main class="flex min-h-screen w-full items-center py-16">
        <div class="max-w-md mx-auto p-6 w-full">
            <div class="border border-gray-200 rounded-xl shadow-2xs">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h3 class="block text-2xl font-bold text-foreground">Reset Password</h3>
                        <p class="mt-2 text-sm text-center text-muted-foreground-2">
                            Enter your new password below
                        </p>
                    </div>

                    <div class="mt-5">
                        <form method="POST" action="{{ route('password.store') }}">
                            @csrf

                            <!-- Password Reset Token -->
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <!-- Email Address -->
                            <x-input-group>
                                <x-input-label for="email" label="Email address"/>
                                <x-text-input
                                    id="email"
                                    name="email"
                                    type="email"
                                    :value="old('email', $request->email)"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                                @error('email')
                                <x-input-error :message="$message" />
                                @enderror
                            </x-input-group>

                            <!-- Password -->
                            <x-input-group>
                                <x-input-label for="password" label="Password"/>
                                <x-text-input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                />
                                @error('password')
                                <x-input-error :message="$message" />
                                @enderror
                            </x-input-group>

                            <!-- Confirm Password -->
                            <x-input-group>
                                <x-input-label for="password_confirmation" label="Confirm Password"/>
                                <x-text-input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                />
                                @error('password_confirmation')
                                <x-input-error :message="$message" />
                                @enderror
                            </x-input-group>

                            <!-- Submit Button -->
                            <x-input-group>
                                <button type="submit"
                                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                    Reset Password
                                </button>
                            </x-input-group>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-auth-layout>

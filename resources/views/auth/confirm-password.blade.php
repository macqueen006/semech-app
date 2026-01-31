<x-auth-layout>
    <main class="flex h-screen w-full items-center py-16">
        <div class="max-w-md mx-auto p-6 w-full">
            <div class="border border-gray-200 rounded-xl shadow-2xs">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h3 class="block text-2xl font-bold text-foreground">Confirm Password</h3>
                        <p class="mt-2 text-sm text-center text-muted-foreground-2">
                            This is a secure area of the application. Please confirm your password before continuing.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.confirm') }}" class="mt-5">
                        @csrf

                        <!-- Password -->
                        <x-input-group>
                            <x-input-label for="password" label="Password"/>
                            <x-text-input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autofocus
                                autocomplete="current-password"
                            />
                            @error('password')
                            <x-input-error :message="$message" />
                            @enderror
                        </x-input-group>

                        <!-- Submit Button -->
                        <x-input-group>
                            <button type="submit"
                                    class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                Confirm
                            </button>
                        </x-input-group>

                        <div class="flex justify-between items-center mt-4 text-sm">
                            <a class="text-secondary hover:underline focus:outline-hidden focus:underline font-medium"
                               href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                            <a class="text-secondary hover:underline focus:outline-hidden focus:underline font-medium"
                               href="{{ url()->previous() }}">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</x-auth-layout>

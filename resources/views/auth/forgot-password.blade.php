<x-auth-layout>
    <main class="flex h-screen w-full items-center py-16">
        <div class="max-w-md mx-auto p-6 w-full">
            <div class="border border-gray-200 rounded-xl shadow-2xs">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h3 class="block text-2xl font-bold text-foreground">Forgot Password?</h3>
                        <p class="mt-2 text-sm text-center text-muted-foreground-2">
                            No problem. Just let us know your email address and we will email you a password reset link.
                        </p>
                    </div>

                    @if (session('status'))
                        <x-alert type="success" class="mt-5">
                            {{ session('status') }}
                        </x-alert>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="mt-5">
                        @csrf

                        <x-input-group>
                            <x-input-label for="email" label="Email address"/>
                            <x-text-input id="email" name="email" type="email" :value="old('email')" required
                                          autofocus/>
                            @error('email')
                            <x-input-error :message="$message"/>
                            @enderror
                        </x-input-group>

                        <x-input-group>
                            <button type="submit"
                                    class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary/90 focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                Email Password Reset Link
                            </button>
                        </x-input-group>

                        <div class="text-center mt-4">
                            <a class="text-sm text-secondary hover:underline focus:outline-hidden focus:underline font-medium"
                               href="{{ route('login') }}">
                                Back to Sign in
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</x-auth-layout>

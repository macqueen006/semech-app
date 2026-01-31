<x-auth-layout>
    <main class="flex min-h-screen w-full items-center py-16">
        <div class="max-w-md mx-auto p-6 w-full">
            <div class="border border-gray-200 rounded-xl shadow-2xs">
                <div class="p-4 sm:p-7">
                    <div class="text-center">
                        <h3 class="block text-2xl font-bold text-foreground">Verify Email Address</h3>
                        <p class="mt-2 text-sm text-center text-muted-foreground-2">
                            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                        </p>
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-600 font-medium">
                                A new verification link has been sent to the email address you provided during registration.
                            </p>
                        </div>
                    @endif

                    <div class="mt-5">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf

                            <x-input-group>
                                <button type="submit"
                                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-secondary border border-gray-200 text-white hover:bg-secondary focus:outline-hidden focus:bg-primary-focus disabled:opacity-50 disabled:pointer-events-none">
                                    Resend Verification Email
                                </button>
                            </x-input-group>
                        </form>

                        <form method="POST" action="{{ route('logout') }}" class="mt-3">
                            @csrf

                            <button type="submit"
                                    class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-gray-200 text-layer-foreground shadow-2xs hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-layer-focus">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-auth-layout>

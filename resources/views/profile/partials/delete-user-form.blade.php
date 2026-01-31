<section class="space-y-6">
    <div class="border border-gray-200 rounded-xl shadow-2xs">
        <div class="p-4 sm:p-7">
            <header>
                <h2 class="text-2xl font-bold text-foreground">
                    {{ __('Delete Account') }}
                </h2>
                <p class="mt-2 text-sm text-muted-foreground-2">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                </p>
            </header>

            <div class="mt-5">
                <button
                    type="button"
                    class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-red-600 border border-red-600 text-white hover:bg-red-700 focus:outline-hidden focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none"
                    aria-haspopup="dialog"
                    aria-expanded="false"
                    aria-controls="confirm-user-deletion"
                    data-hs-overlay="#confirm-user-deletion">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </div>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="flex justify-between items-center py-3 px-4 border-b border-overlay-header">
                <h3 id="confirm-user-deletion-label" class="text-xl font-semibold text-foreground">
                    {{ __('Are you sure you want to delete your account?') }}
                </h3>
                <button
                    type="button"
                    class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full bg-surface border border-gray-400 text-gray-400 hover:bg-surface-hover focus:outline-hidden focus:bg-surface-focus disabled:opacity-50 disabled:pointer-events-none"
                    aria-label="Close"
                    data-hs-overlay="#confirm-user-deletion">
                    <span class="sr-only">Close</span>
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-4 overflow-y-auto">
                <p class="text-sm text-muted-foreground-2">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="mt-4">
                    <x-input-label for="password" label="Password" class="sr-only" />
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="{{ __('Password') }}"
                    />
                    @error('password', 'userDeletion')
                    <x-input-error :message="$message" class="mt-2" />
                    @enderror
                </div>
            </div>

            <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t border-gray-200">
                <button
                    type="button"
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-gray-400 text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                    data-hs-overlay="#confirm-user-deletion">
                    {{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg bg-red-600 border border-red-600 text-white hover:bg-red-700 focus:outline-hidden focus:bg-red-700 disabled:opacity-50 disabled:pointer-events-none">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

<x-guest-layout>
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <div class="mb-4 text-muted small">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input
                        id="password"
                        class="mt-1"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2"
                    />
                </div>

                <div class="d-flex justify-content-end">
                    <x-primary-button>
                        {{ __('Confirm') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>
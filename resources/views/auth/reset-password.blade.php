<x-guest-layout>
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <input
                    type="hidden"
                    name="token"
                    value="{{ $request->route('token') }}"
                >

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />

                    <x-text-input
                        id="email"
                        class="mt-1"
                        type="email"
                        name="email"
                        :value="old('email', $request->email)"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-2"
                    />
                </div>

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input
                        id="password"
                        class="mt-1"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password')"
                        class="mt-2"
                    />
                </div>

                <div class="mb-4">
                    <x-input-label
                        for="password_confirmation"
                        :value="__('Confirm Password')"
                    />

                    <x-text-input
                        id="password_confirmation"
                        class="mt-1"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-error
                        :messages="$errors->get('password_confirmation')"
                        class="mt-2"
                    />
                </div>

                <div class="d-flex justify-content-end">
                    <x-primary-button>
                        {{ __('Reset Password') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>
<x-guest-layout>
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <div class="mb-4 text-muted small">
                {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
            </div>

            <x-auth-session-status
                class="mb-3"
                :status="session('status')"
            />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />

                    <x-text-input
                        id="email"
                        class="mt-1"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <x-input-error
                        :messages="$errors->get('email')"
                        class="mt-2"
                    />
                </div>

                <div class="d-flex justify-content-end">
                    <x-primary-button>
                        {{ __('Email Password Reset Link') }}
                    </x-primary-button>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>
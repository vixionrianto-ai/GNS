<x-guest-layout>
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <h2 class="h4 fw-semibold text-dark mb-2">Masuk ke GNS</h2>
                <p class="small text-muted mb-0">
                    Silakan gunakan email dan password Anda untuk masuk.
                </p>
            </div>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input
                        id="email"
                        class="w-100"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div class="mb-3">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input
                        id="password"
                        class="w-100"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input
                            id="remember_me"
                            type="checkbox"
                            class="form-check-input"
                            name="remember"
                        >
                        <label class="form-check-label small" for="remember_me">
                            {{ __('Remember me') }}
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a
                            class="small text-decoration-none"
                            href="{{ route('password.request') }}"
                        >
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <x-primary-button class="w-100">
                    {{ __('Log in') }}
                </x-primary-button>
                <div class="text-center mt-3">
                    <span class="small text-muted">Belum punya akun?</span>
                    <a
                        href="{{ route('register') }}"
                        class="small text-decoration-none fw-semibold"
                    >
                        Daftar
                    </a>
                </div>
            </form>

        </div>
    </div>
</x-guest-layout>
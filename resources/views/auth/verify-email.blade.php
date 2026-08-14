<x-guest-layout>
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4 p-md-5">

            <div class="mb-4 text-muted small">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success small mb-4">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between gap-2">

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <x-primary-button>
                        {{ __('Resend Verification Email') }}
                    </x-primary-button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-link text-secondary text-decoration-none"
                    >
                        {{ __('Log Out') }}
                    </button>
                </form>

            </div>

        </div>
    </div>
</x-guest-layout>
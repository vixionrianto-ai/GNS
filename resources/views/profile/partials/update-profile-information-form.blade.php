<section>

    <div class="mb-4">
        <h5 class="mb-1">
            {{ __('Profile Information') }}
        </h5>

        <p class="text-muted small mb-0">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <x-input-label for="name" :value="__('Name')" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1"
                :value="old('name', $user->name)"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('name')"
            />
        </div>

        <div class="mb-3">

            <x-input-label for="email" :value="__('Email')" />

            <x-text-input
                id="email"
                name="email"
                type="email"
                class="mt-1"
                :value="old('email', $user->email)"
                required
                autocomplete="username"
            />

            <x-input-error
                class="mt-2"
                :messages="$errors->get('email')"
            />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())

                <div class="alert alert-warning mt-3 small">

                    {{ __('Your email address is unverified.') }}

                    <button
                        form="send-verification"
                        type="submit"
                        class="btn btn-link btn-sm p-0 align-baseline"
                    >
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success small">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </div>
                @endif

            @endif

        </div>

        <div class="d-flex align-items-center gap-3">

            <x-primary-button>
                {{ __('Save') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">
                    {{ __('Saved.') }}
                </span>
            @endif

        </div>

    </form>

</section>
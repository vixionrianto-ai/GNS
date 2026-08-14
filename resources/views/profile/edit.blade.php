<x-app-layout>

    <x-slot name="header">
        <h2 class="h4 mb-0 fw-semibold">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">

        <div class="row justify-content-center">

            <div class="col-lg-8">

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>

        </div>

    </div>

</x-app-layout>
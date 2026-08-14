<section>

    <div class="mb-4">
        <h5 class="mb-1 text-danger">
            {{ __('Delete Account') }}
        </h5>

        <p class="text-muted small mb-0">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </div>

    <button
        type="button"
        class="btn btn-danger"
        data-bs-toggle="modal"
        data-bs-target="#confirmUserDeletion"
    >
        {{ __('Delete Account') }}
    </button>

    <div
        class="modal fade"
        id="confirmUserDeletion"
        tabindex="-1"
        aria-labelledby="confirmUserDeletionLabel"
        aria-hidden="true"
    >

        <div class="modal-dialog">

            <div class="modal-content">

                <form
                    method="post"
                    action="{{ route('profile.destroy') }}"
                >

                    @csrf
                    @method('delete')

                    <div class="modal-header">

                        <h5
                            class="modal-title"
                            id="confirmUserDeletionLabel"
                        >
                            {{ __('Are you sure you want to delete your account?') }}
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>

                    </div>

                    <div class="modal-body">

                        <p class="text-muted small">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mb-3">

                            <x-input-label
                                for="delete_password"
                                :value="__('Password')"
                            />

                            <x-text-input
                                id="delete_password"
                                name="password"
                                type="password"
                                class="mt-1"
                                placeholder="{{ __('Password') }}"
                            />

                            <x-input-error
                                :messages="$errors->userDeletion->get('password')"
                                class="mt-2"
                            />

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            {{ __('Cancel') }}
                        </button>

                        <x-danger-button>
                            {{ __('Delete Account') }}
                        </x-danger-button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</section>
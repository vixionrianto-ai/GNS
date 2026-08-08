@extends('layouts.app')

@section('title', 'Super Admin')

@section('content')

<div class="container-fluid">

    <div class="card card-danger">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle"></i>
                SUPER ADMIN
            </h3>
        </div>

        <div class="card-body">

            <div class="alert alert-danger">

                <h5><i class="icon fas fa-ban"></i> Danger Zone</h5>

                Semua data yang dipilih akan dihapus permanen.

            </div>

            @if(session('success'))

                <div class="alert alert-success">

                    {{ session('success') }}

                </div>

            @endif

            <form
                method="POST"
                action="{{ route('superadmin.reset') }}"
            >

                @csrf

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="pelanggan"
                        value="1">

                    <label class="form-check-label">

                        Hapus Semua Pelanggan

                    </label>

                </div>

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="tagihan"
                        value="1">

                    <label class="form-check-label">

                        Hapus Semua Tagihan

                    </label>

                </div>

                <div class="form-check">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="pembayaran"
                        value="1">

                    <label class="form-check-label">

                        Hapus Semua Pembayaran

                    </label>

                </div>

                <hr>

                <div class="form-group">

                    <label>

                        Ketik

                        <strong>RESET GNS</strong>

                    </label>

                    <input
                        type="text"
                        name="confirm"
                        class="form-control">

                </div>

                <button
                    class="btn btn-danger">

                    RESET DATA

                </button>

            </form>

        </div>

    </div>

</div>

@endsection
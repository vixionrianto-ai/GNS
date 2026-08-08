@extends('adminlte::page')

@section('title', 'Backup Database')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <h1>
        <i class="fas fa-database text-primary"></i>
        Backup Database
    </h1>

    <form
        action="{{ route('backup.create') }}"
        method="POST">

        @csrf

        <button
            class="btn btn-success">

            <i class="fas fa-download"></i>

            Backup Sekarang

        </button>

    </form>

</div>

@stop

@section('content')

@if(session('success'))

<div class="alert alert-success">

    {{ session('success') }}

</div>

@endif
<div class="card border-warning mb-3">

    <div class="card-header bg-warning">

        <h3 class="card-title">

            <i class="fas fa-upload"></i>

            Restore Database

        </h3>

    </div>

    <div class="card-body">

        <div class="alert alert-danger">

            <strong>Perhatian!</strong><br>

            Restore akan menimpa seluruh isi database saat ini.<br>

            Sistem akan membuat backup otomatis sebelum proses restore dimulai.

        </div>

        <form
            action="{{ route('backup.restore') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="form-group">

                <label>Pilih File Backup (.sql)</label>

                <input
                    type="file"
                    name="backup_file"
                    class="form-control"
                    accept=".sql"
                    required>

            </div>

            <button
                type="submit"
                class="btn btn-warning"
                onclick="return confirm('Yakin ingin melakukan restore database?')">

                <i class="fas fa-upload"></i>

                Restore Database

            </button>

        </form>

    </div>

</div>

<div class="card shadow-sm">

    <div class="card-header">

        <h3 class="card-title">

            Daftar Backup Database

        </h3>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered mb-0">

                <thead>

                    <tr>

                        <th width="60">No</th>

                        <th>Nama File</th>

                        <th width="180">Ukuran</th>

                        <th width="180">Tanggal</th>

                        <th width="180">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                @forelse($files as $file)

                    <tr>

                        <td>

                            {{ $loop->iteration }}

                        </td>

                        <td>

                            <i class="fas fa-file-alt text-primary"></i>

                            {{ $file['name'] }}

                        </td>

                        <td>

                            {{ $file['size'] }} MB

                        </td>

                        <td>

                            {{ $file['date'] }}

                        </td>

                        <td>

                            <a
                                href="{{ route('backup.download',$file['name']) }}"
                                class="btn btn-sm btn-primary">

                                <i class="fas fa-download"></i>

                            </a>

                            <form
                                action="{{ route('backup.destroy',$file['name']) }}"
                                method="POST"
                                class="d-inline">

                                @csrf
                                @method('DELETE')

                                <button
                                    onclick="return confirm('Hapus backup ini?')"
                                    class="btn btn-sm btn-danger">

                                    <i class="fas fa-trash"></i>

                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="text-center text-muted py-5">

                            <i class="fas fa-folder-open fa-3x mb-3"></i>

                            <br>

                            Belum ada file backup.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@stop
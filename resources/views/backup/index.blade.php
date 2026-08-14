@extends('layouts.app')

@section('title', 'Backup Database')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-database text-primary me-2"></i>Backup & Restore Database
            </h2>
            <p class="text-secondary mb-0">Kelola pencadangan dan pemulihan database GNS Enterprise.</p>
        </div>

        <form action="{{ route('backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="fas fa-download me-1"></i> Backup Sekarang
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Proses gagal.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Backup Database</h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary">Buat salinan database saat ini. File akan disimpan di penyimpanan backup aplikasi.</p>
                    <form action="{{ route('backup.create') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i> Buat Backup Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5" id="restore">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Restore Database</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <strong>Perhatian!</strong><br>
                        Restore akan menimpa database saat ini. Sistem membuat backup otomatis sebelum restore dimulai.
                    </div>

                    <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" id="restoreForm">
                        @csrf
                        <div class="mb-3">
                            <label for="backup_file" class="form-label fw-semibold">Pilih File Backup (.sql)</label>
                            <input type="file" name="backup_file" id="backup_file" class="form-control" accept=".sql" required>
                        </div>

                        <button type="submit" class="btn btn-warning" id="restoreButton">
                            <i class="fas fa-upload me-1"></i> Restore Database
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Daftar Backup Database</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="70">No</th>
                            <th>Nama File</th>
                            <th width="140">Ukuran</th>
                            <th width="190">Tanggal</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($files as $file)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <i class="fas fa-file-code text-primary me-2"></i>{{ $file['name'] }}
                            </td>
                            <td>{{ $file['size'] }} MB</td>
                            <td>{{ $file['date'] }}</td>
                            <td>
                                <a href="{{ route('backup.download', $file['name']) }}" class="btn btn-sm btn-primary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('backup.destroy', $file['name']) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Hapus backup ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-2x mb-2"></i><br>
                                Belum ada file backup.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('restoreForm')?.addEventListener('submit', function (event) {
    if (!confirm('Yakin ingin melakukan restore database? Database saat ini akan ditimpa. Backup otomatis akan dibuat terlebih dahulu.')) {
        event.preventDefault();
        return;
    }

    const button = document.getElementById('restoreButton');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
    }
});
</script>
@endpush

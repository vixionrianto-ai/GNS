@extends('layouts.app')

@section('title', 'Reset Data')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-trash-alt text-danger me-2"></i>Reset Data
            </h2>
            <p class="text-secondary mb-0">Hapus data transaksi secara terkontrol sebagai Super Admin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Reset tidak dapat diproses.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-danger shadow-sm">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
            </h5>
        </div>

        <div class="card-body">
            <div class="alert alert-warning">
                <strong>Perhatian!</strong><br>
                Data yang dipilih akan dihapus permanen. Proses ini tidak dapat dibatalkan.
                Data yang tidak dicentang tidak akan disentuh.
            </div>

            <form method="POST" action="{{ route('superadmin.reset') }}" id="resetForm">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pelanggan" value="1" id="resetPelanggan" {{ old('pelanggan') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="resetPelanggan">
                                    <i class="fas fa-users text-primary me-2"></i>Hapus Semua Pelanggan
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Termasuk tagihan, pembayaran, saldo, alokasi, dan log terkait pelanggan.</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tagihan" value="1" id="resetTagihan" {{ old('tagihan') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="resetTagihan">
                                    <i class="fas fa-file-invoice-dollar text-warning me-2"></i>Hapus Semua Tagihan
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Termasuk pembayaran, alokasi, saldo usage, dan log WhatsApp terkait tagihan.</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pembayaran" value="1" id="resetPembayaran" {{ old('pembayaran') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="resetPembayaran">
                                    <i class="fas fa-wallet text-success me-2"></i>Hapus Semua Pembayaran
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">Tagihan tetap ada dan statusnya dihitung ulang setelah pembayaran dihapus.</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label for="confirm" class="form-label">
                        Ketik <strong>RESET GNS</strong> untuk melanjutkan
                    </label>
                    <input type="text" name="confirm" id="confirm" class="form-control" value="{{ old('confirm') }}" autocomplete="off" required>
                </div>

                <button type="submit" class="btn btn-danger" id="resetButton">
                    <i class="fas fa-trash-alt me-1"></i> RESET DATA
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('resetForm')?.addEventListener('submit', function (event) {
    const confirmText = document.getElementById('confirm')?.value?.trim();
    const selected = this.querySelectorAll('input[type="checkbox"]:checked').length;

    if (selected === 0) {
        event.preventDefault();
        alert('Pilih minimal satu jenis data yang akan direset.');
        return;
    }

    if (confirmText !== 'RESET GNS') {
        event.preventDefault();
        alert('Konfirmasi harus diketik tepat: RESET GNS');
        return;
    }

    if (!confirm('PERINGATAN: Data yang dipilih akan dihapus permanen. Lanjutkan?')) {
        event.preventDefault();
        return;
    }

    const button = document.getElementById('resetButton');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
    }
});
</script>
@endpush

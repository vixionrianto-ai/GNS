<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tagihan</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .muted { color: #666; margin-bottom: 14px; }
        .filters { margin-bottom: 12px; padding: 8px; background: #f4f6f8; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #eef2f6; font-weight: bold; }
        th, td { border: 1px solid #d9dee3; padding: 5px; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Tagihan GNS Network</h1>
    <div class="muted">Dicetak {{ now()->format('d-m-Y H:i') }}</div>

    @php
        $activeFilters = collect([
            'Dari' => $filters['tanggal_awal'] ?? null,
            'Sampai' => $filters['tanggal_akhir'] ?? null,
            'Bulan' => $filters['bulan'] ?? null,
            'Tahun' => $filters['tahun'] ?? null,
            'Status' => $filters['status'] ?? null,
            'Cari' => $filters['search'] ?? null,
        ])->filter(fn($value) => filled($value));
    @endphp

    @if($activeFilters->isNotEmpty())
        <div class="filters">
            <strong>Filter:</strong>
            @foreach($activeFilters as $label => $value)
                {{ $label }}: {{ $value }}{{ !$loop->last ? ' | ' : '' }}
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Periode</th>
                <th class="right">Total</th>
                <th class="right">Dibayar</th>
                <th class="right">Sisa</th>
                <th>Status</th>
                <th>Jatuh Tempo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ optional($item->tanggal_tagihan)->format('d-m-Y') }}</td>
                    <td>{{ $item->invoice_no }}</td>
                    <td>{{ optional($item->pelanggan)->nama }}</td>
                    <td>{{ optional(optional($item->pelanggan)->paket)->nama ?? '-' }}</td>
                    <td>{{ $item->periode }}</td>
                    <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->dibayar, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->sisa, 0, ',', '.') }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ optional($item->tanggal_jatuh_tempo)->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="center">Tidak ada data tagihan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

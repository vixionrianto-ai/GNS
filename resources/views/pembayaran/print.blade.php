<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice {{ $pembayaran->invoice_no }}</title>
<style>
    @page { size: A4 portrait; margin: 12mm; }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        color: #1f2937;
        background: #fff;
        font-size: 12px;
    }
    .invoice {
        width: 100%;
        max-width: 190mm;
        margin: 0 auto;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 10px;
        border-bottom: 3px solid #1565c0;
    }
    .brand { font-size: 22px; font-weight: 700; color: #1565c0; }
    .brand-sub { margin-top: 3px; color: #6b7280; font-size: 10px; }
    .invoice-meta { text-align: right; }
    .invoice-title { font-size: 18px; font-weight: 700; color: #1565c0; }
    .invoice-no { margin-top: 4px; font-weight: 700; font-size: 13px; }
    .status { margin-top: 6px; display: inline-block; padding: 5px 10px; border-radius: 4px; background: #198754; color: #fff; font-weight: 700; }
    .section { margin-top: 12px; border: 1px solid #d6dbe1; border-radius: 5px; overflow: hidden; }
    .section-title { padding: 7px 9px; background: #1565c0; color: #fff; font-weight: 700; }
    .section-body { padding: 9px; }
    .two-col { width: 100%; border-collapse: collapse; }
    .two-col td { width: 50%; vertical-align: top; padding: 3px 8px 3px 0; }
    .label { color: #6b7280; font-size: 10px; }
    .value { font-weight: 700; margin-top: 2px; }
    table.detail { width: 100%; border-collapse: collapse; }
    table.detail th, table.detail td { border: 1px solid #d6dbe1; padding: 7px; }
    table.detail th { background: #eaf2ff; color: #17324d; text-align: left; }
    table.detail td.num, table.detail th.num { text-align: right; }
    .summary { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .summary td { padding: 5px 7px; border-bottom: 1px solid #e5e7eb; }
    .summary td:first-child { font-weight: 700; width: 75%; }
    .grand td { font-size: 15px; color: #1565c0; font-weight: 700; border-top: 2px solid #1565c0; }
    .footer { margin-top: 18px; display: flex; justify-content: space-between; gap: 20px; }
    .thanks { color: #4b5563; line-height: 1.5; }
    .signature { width: 190px; text-align: center; }
    .signature-line { margin-top: 42px; border-top: 1px solid #374151; padding-top: 4px; font-weight: 700; }
    .no-print { margin-bottom: 12px; text-align: right; }
    .no-print button { border: 0; border-radius: 5px; padding: 8px 14px; font-weight: 700; cursor: pointer; background: #1565c0; color: #fff; }
    @media print {
        .no-print { display: none !important; }
        body { background: #fff; }
    }
</style>
</head>
<body>
<div class="no-print"><button type="button" onclick="window.print()">Cetak Invoice</button></div>
<div class="invoice">
    <div class="header">
        <div>
            <div class="brand">GNS NETWORK</div>
            <div class="brand-sub">Internet Service Provider &amp; Billing System</div>
        </div>
        <div class="invoice-meta">
            <div class="invoice-title">INVOICE PEMBAYARAN</div>
            <div class="invoice-no">{{ $pembayaran->invoice_no }}</div>
            <div class="status">{{ strtoupper($pembayaran->status) }}</div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">INFORMASI PELANGGAN</div>
        <div class="section-body">
            <table class="two-col">
                <tr>
                    <td><div class="label">Nama Pelanggan</div><div class="value">{{ optional(optional($pembayaran->tagihan)->pelanggan)->nama ?? '-' }}</div></td>
                    <td><div class="label">No. HP</div><div class="value">{{ optional(optional($pembayaran->tagihan)->pelanggan)->no_hp ?? '-' }}</div></td>
                </tr>
                <tr>
                    <td><div class="label">Username PPPoE</div><div class="value">{{ optional(optional($pembayaran->tagihan)->pelanggan)->username_pppoe ?? '-' }}</div></td>
                    <td><div class="label">Paket Internet</div><div class="value">{{ optional(optional(optional($pembayaran->tagihan)->pelanggan)->paket)->nama_paket ?? '-' }}</div></td>
                </tr>
                <tr>
                    <td><div class="label">Alamat</div><div class="value">{{ optional(optional($pembayaran->tagihan)->pelanggan)->alamat ?? '-' }}</div></td>
                    <td><div class="label">Tanggal Pembayaran</div><div class="value">{{ optional($pembayaran->tanggal_bayar)->format('d F Y') ?? '-' }}</div></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DETAIL TAGIHAN YANG DIBAYAR</div>
        <div class="section-body">
            <table class="detail">
                <thead>
                    <tr>
                        <th style="width: 8%">No</th>
                        <th style="width: 20%">Periode</th>
                        <th>Invoice Tagihan</th>
                        <th style="width: 22%">Paket</th>
                        <th class="num" style="width: 20%">Dibayar</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($alokasiTagihan as $index => $alokasi)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ optional($alokasi->tagihan)->periode ?? '-' }}</td>
                        <td>{{ optional($alokasi->tagihan)->invoice_no ?? '-' }}</td>
                        <td>{{ optional(optional(optional($alokasi->tagihan)->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                        <td class="num">Rp {{ number_format((float) $alokasi->nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td>1</td>
                        <td>{{ optional($pembayaran->tagihan)->periode ?? '-' }}</td>
                        <td>{{ optional($pembayaran->tagihan)->invoice_no ?? '-' }}</td>
                        <td>{{ optional(optional(optional($pembayaran->tagihan)->pelanggan)->paket)->nama_paket ?? '-' }}</td>
                        <td class="num">Rp {{ number_format((float) ($pembayaran->nominal ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>Metode Pembayaran</td>
            <td>{{ $pembayaran->metode ?? '-' }}</td>
        </tr>
        <tr>
            <td>Biaya Admin</td>
            <td>Rp {{ number_format((float) ($pembayaran->biaya_admin ?? 0), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Dibayar</td>
            <td>Rp {{ number_format((float) ($pembayaran->dibayar ?? 0), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td>Rp {{ number_format((float) ($pembayaran->kembalian ?? 0), 0, ',', '.') }}</td>
        </tr>
        <tr class="grand">
            <td>TOTAL PEMBAYARAN</td>
            <td>Rp {{ number_format((float) ($pembayaran->total_bayar ?? 0), 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <div class="thanks">
            <strong>Terima kasih telah melakukan pembayaran.</strong><br>
            Invoice ini dibuat secara otomatis oleh GNS NETWORK.<br>
            Dokumen sah tanpa tanda tangan dan stempel.
        </div>
        <div class="signature">
            Hormat Kami,
            <div class="signature-line">{{ optional($pembayaran->user)->name ?? 'Administrator' }}</div>
            <div>Administrator GNS</div>
        </div>
    </div>
</div>
</body>
</html>

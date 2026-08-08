<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $pembayaran->invoice_no }}</title>
<style>
@page {
    margin: 12px 15px;
}
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    line-height: 1.25;
    color: #2c3e50;
    margin: 0;
    padding: 0;
}
* {
    box-sizing: border-box;
}
.clear {
    clear: both;
}
.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
.bold {
    font-weight: bold;
}
.small {
    font-size: 9px;
}

/* Header */
.header {
    border-bottom: 3px solid #1565C0;
    padding-bottom: 8px;
    margin-bottom: 8px;
}
.brand-row {
    width: 100%;
}
.logo {
    width: 90px;
    max-height: 50px;
    object-fit: contain;
    float: left;
}
.company {
    float: right;
    text-align: right;
}
.company-name {
    font-size: 20px;
    color: #1565C0;
    font-weight: bold;
    line-height: 1;
}
.company-desc {
    font-size: 10px;
    color: #3f4a57;
    margin-top: 3px;
}

/* Invoice Title & Status */
.invoice-box {
    width: 100%;
    margin-bottom: 8px;
}
.invoice-title-left {
    float: left;
}
.invoice-title-left h1 {
    font-size: 16px;
    color: #1565C0;
    margin: 0 0 2px 0;
}
.invoice-number {
    font-size: 14px;
    color: #1565C0;
    font-weight: bold;
}
.invoice-status-right {
    float: right;
    text-align: right;
}
.status {
    background: #28a745;
    color: white;
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}

/* Info Card Pelanggan */
.info-card {
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    margin-bottom: 8px;
    overflow: hidden;
}
.card-header {
    background: #1565C0;
    color: white;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: bold;
}
.card-body {
    padding: 8px;
}
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table td {
    padding: 3px 5px;
    border-bottom: 1px solid #e5e5e5;
    vertical-align: top;
}
.info-table td:first-child {
    width: 35%;
    font-weight: bold;
    color: #2f3b4c;
}

/* Tabel Rincian & Summary */
.rincian, .summary {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
}
.rincian th {
    background: #1565C0;
    color: white;
    padding: 5px;
    border: 1px solid #d8d8d8;
    font-size: 9px;
}
.rincian td, .summary td {
    border: 1px solid #d8d8d8;
    padding: 5px;
}
.summary td:first-child {
    width: 75%;
    font-weight: bold;
}
.total {
    color: #1565C0;
    font-size: 12px;
    font-weight: bold;
}
.green {
    color: #28a745;
    font-weight: bold;
}

/* Watermark */
.watermark {
    position: fixed;
    top: 250px;
    left: 150px;
    transform: rotate(-35deg);
    font-size: 80px;
    color: #e9e9e9;
    opacity: 0.25;
    z-index: -1000;
}

/* Footer */
.footer {
    margin-top: 10px;
    width: 100%;
}
.footer-left {
    float: left;
    width: 65%;
}
.signature {
    float: right;
    width: 30%;
    text-align: center;
}
.signature hr {
    margin-top: 35px;
    margin-bottom: 2px;
}
.note {
    float: left;
    width: 100%;
    font-size: 9px;
    color: #555;
}
</style>
</head>
<body>

<div class="watermark">{{ strtoupper($pembayaran->status) }}</div>

<!-- Header -->
<div class="header">
    <div class="brand-row">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" class="logo">
        @endif
        <div class="company">
            <div class="company-name">GNS NETWORK</div>
            <div class="company-desc">Internet Service Provider & Billing System</div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<!-- Invoice & Status -->
<div class="invoice-box">
    <div class="invoice-title-left">
        <h1>INVOICE PEMBAYARAN</h1>
        <div class="invoice-number">{{ $pembayaran->invoice_no }}</div>
    </div>
    <div class="invoice-status-right">
        <div class="status">{{ strtoupper($pembayaran->status) }}</div>
    </div>
    <div class="clear"></div>
</div>

<!-- Data Pelanggan & Informasi -->
<div class="info-card">
    <div class="card-header">DATA PELANGGAN & INVOICE</div>
    <div class="card-body">
        <table class="info-table" style="float: left; width: 49%;">
            <tr>
                <td>Nama Pelanggan</td>
                <td>{{ optional(optional($pembayaran->tagihan)->pelanggan)->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Username PPPoE</td>
                <td>{{ optional(optional($pembayaran->tagihan)->pelanggan)->username_pppoe ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>{{ optional(optional($pembayaran->tagihan)->pelanggan)->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td>No HP</td>
                <td>{{ optional(optional($pembayaran->tagihan)->pelanggan)->no_hp ?: '-' }}</td>
            </tr>
        </table>
        <table class="info-table" style="float: right; width: 49%;">
            <tr>
                <td>Paket Internet</td>
                <td>{{ optional(optional(optional($pembayaran->tagihan)->pelanggan)->paket)->nama_paket ?? '-' }}</td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>{{ optional($pembayaran->tagihan)->periode ?? '-' }}</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td>{{ $pembayaran->metode ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>{{ optional($pembayaran->user)->name ?? '-' }}</td>
            </tr>
        </table>
        <div class="clear"></div>
    </div>
</div>

<!-- Tabel Rincian -->
<table class="rincian">
    <thead>
        <tr>
            <th width="6%">NO</th>
            <th width="44%">KETERANGAN</th>
            <th width="18%">NOMINAL</th>
            <th width="15%">ADMIN</th>
            <th width="17%">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">1</td>
            <td>
                <strong>Tagihan Internet</strong><br>
                <span class="small">{{ optional(optional(optional($pembayaran->tagihan)->pelanggan)->paket)->nama_paket ?? '-' }}</span>
            </td>
            <td class="text-right">Rp {{ number_format($pembayaran->nominal ?? 0, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($pembayaran->biaya_admin ?? 0, 0, ',', '.') }}</td>
            <td class="text-right total">Rp {{ number_format($pembayaran->total_bayar ?? 0, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

<!-- Tabel Summary -->
<table class="summary">
    <tr>
        <td>TOTAL TAGIHAN</td>
        <td class="text-right total">Rp {{ number_format($pembayaran->total_bayar ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>DIBAYAR</td>
        <td class="text-right total">Rp {{ number_format($pembayaran->dibayar ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>KEMBALIAN</td>
        <td class="text-right green">Rp {{ number_format($pembayaran->kembalian ?? 0, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>KETERANGAN</td>
        <td class="text-right">{{ $pembayaran->keterangan ?: '-' }}</td>
    </tr>
</table>

<!-- Footer & Tanda Tangan -->
<div class="footer">
    <div class="footer-left">
        <div class="note" style="width: 100%;">
            <strong>Terima kasih telah melakukan pembayaran.</strong><br>
            Invoice ini dibuat secara otomatis oleh <strong>GNS NETWORK</strong>.<br>
            Dokumen sah tanpa tanda tangan dan stempel.
        </div>
        <div class="clear"></div>
    </div>
    <div class="signature">
        Hormat Kami,<br><br>
        <hr>
        <strong>{{ optional($pembayaran->user)->name ?? 'Administrator' }}</strong><br>
        <span class="small">Administrator GNS</span>
    </div>
    <div class="clear"></div>
</div>

</body>
</html>
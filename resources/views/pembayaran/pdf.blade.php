<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<title>Invoice {{ $pembayaran->invoice_no }}</title>

<style>

@page{
    margin:20px;
}

body{
    font-family: DejaVu Sans;
    font-size:12px;
    line-height:1.4;
    color:#2c3e50;
    margin:0;
    padding:0;
}

*{
    box-sizing:border-box;
}

.clear{
    clear:both;
}

.text-right{
    text-align:right;
}

.text-center{
    text-align:center;
}

.bold{
    font-weight:bold;
}

.small{
    font-size:11px;
}

.mt-10{
    margin-top:10px;
}

.mt-20{
    margin-top:20px;
}

.mt-30{
    margin-top:30px;
}

.mb-20{
    margin-bottom:20px;
}

.header{
    border-bottom:4px solid #1565C0;
    padding-bottom:15px;
    overflow:hidden;
}

.brand-row{
    display:flex;
    align-items:flex-start;
    gap:18px;
}

.logo{
    width:120px;
    max-height:95px;
    object-fit:contain;
    flex-shrink:0;
}

.company{
    flex:1;
}

.company-name{
    font-size:30px;
    color:#1565C0;
    font-weight:bold;
    line-height:1.1;
}

.company-desc{
    font-size:15px;
    margin-top:3px;
    color:#3f4a57;
}

.company-contact{
    margin-top:8px;
    font-size:12px;
    color:#555;
}

.invoice-box{
    margin-top:22px;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
}

.invoice-title{
    flex:1;
}

.invoice-title h1{
    font-size:28px;
    color:#1565C0;
    margin:0 0 8px 0;
}

.invoice-status{
    flex-shrink:0;
}

.status{
    background:#28a745;
    color:white;
    padding:8px 20px;
    border-radius:6px;
    font-size:16px;
    font-weight:bold;
    display:inline-block;
}

.info-card{
    margin-top:18px;
    border:1px solid #d9d9d9;
    border-radius:6px;
    overflow:hidden;
}

.card-header{
    background:#1565C0;
    color:white;
    padding:10px 15px;
    font-size:15px;
    font-weight:bold;
}

.card-body{
    padding:15px;
}

.info-grid{
    display:flex;
    gap:4%;
}

.left{
    width:48%;
    float:none;
}

.right{
    width:48%;
    float:none;
}

.info-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.info-table td{
    padding:7px;
    border-bottom:1px solid #e5e5e5;
    vertical-align:top;
}

.info-table td:first-child{
    width:36%;
    font-weight:bold;
    color:#2f3b4c;
}

.invoice-number{
    font-size:24px;
    color:#1565C0;
    font-weight:bold;
    margin-top:8px;
}

.barcode{
    margin-top:15px;
    border:1px solid #444;
    height:50px;
    text-align:center;
    line-height:50px;
    letter-spacing:4px;
    font-weight:bold;
}

.watermark{
    position:fixed;
    top:320px;
    left:110px;
    transform:rotate(-35deg);
    font-size:110px;
    color:#e9e9e9;
    opacity:0.35;
}

.rincian{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

.rincian th{
    background:#1565C0;
    color:white;
    padding:10px;
    border:1px solid #d8d8d8;
}

.rincian td{
    border:1px solid #d8d8d8;
    padding:10px;
}

.bg-blue{
    background:#1565C0;
    color:white;
}

.summary{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

.summary td{
    border:1px solid #d8d8d8;
    padding:10px;
}

.summary td:first-child{
    width:75%;
    font-weight:bold;
}

.total{
    color:#1565C0;
    font-size:18px;
    font-weight:bold;
}

.green{
    color:#28a745;
    font-weight:bold;
}

.footer{
    margin-top:30px;
    display:flex;
    justify-content:space-between;
    gap:20px;
    align-items:flex-start;
}

.footer-left{
    flex:1;
}

.signature{
    width:260px;
    float:none;
    text-align:center;
    flex-shrink:0;
}

.signature hr{
    margin-top:70px;
}

.qr{
    width:95px;
    height:95px;
    border:1px solid #d8d8d8;
    padding:5px;
}

.note{
    margin-left:0;
}

.bottom{
    margin-top:25px;
    border-top:2px solid #1565C0;
    padding-top:8px;
    font-size:11px;
    text-align:center;
    color:#666;
}

</style>

</head>

<body>

<div class="watermark">

LUNAS

</div>

<div class="header">

<div class="brand-row">

@if(file_exists(public_path('images/logo.png')))

<img
src="{{ public_path('images/logo.png') }}"
class="logo">

@endif

<div class="company">

<div class="company-name">

GNS NETWORK

</div>

<div class="company-desc">

Internet Service Provider

</div>

<div class="company-desc">

Billing Management System

</div>

<div class="company-contact">

Indonesia • Telp : 08xxxxxxxxxx • Email : info@gns.net

</div>

</div>

</div>

<div class="clear"></div>

</div>
<div class="invoice-box">

<div class="invoice-title">

<h1>

INVOICE PEMBAYARAN

</h1>

<div class="invoice-number">

{{ $pembayaran->invoice_no }}

</div>

<div class="barcode">

|||| {{ $pembayaran->invoice_no }} ||||

</div>

</div>

<div class="invoice-status">

<div class="status">

{{ strtoupper($pembayaran->status) }}

</div>

</div>

</div>

<div class="info-card">

<div class="card-header">

DATA PELANGGAN

</div>

<div class="card-body">

<div class="info-grid">

<div class="left">

<table class="info-table">

<tr>

<td>Nama Pelanggan</td>

<td>

{{ $pembayaran->tagihan->pelanggan->nama }}

</td>

</tr>

<tr>

<td>Username PPPoE</td>

<td>

{{ $pembayaran->tagihan->pelanggan->username_pppoe }}

</td>

</tr>

<tr>

<td>Alamat</td>

<td>

{{ $pembayaran->tagihan->pelanggan->alamat }}

</td>

</tr>

<tr>

<td>No HP</td>

<td>

{{ $pembayaran->tagihan->pelanggan->no_hp ?: '-' }}

</td>

</tr>

<tr>

<td>Router</td>

<td>

{{ $pembayaran->tagihan->pelanggan->router->nama_router ?? '-' }}

</td>

</tr>

</table>

</div>

<div class="right">

<table class="info-table">

<tr>

<td>Paket Internet</td>

<td>

{{ $pembayaran->tagihan->pelanggan->paket->nama_paket }}

</td>

</tr>

<tr>

<td>Periode</td>

<td>

{{ $pembayaran->tagihan->periode }}

</td>

</tr>

<tr>

<td>Metode</td>

<td>

{{ $pembayaran->metode }}

</td>

</tr>

<tr>

<td>Status</td>

<td>

<span class="green">

{{ strtoupper($pembayaran->status) }}

</span>

</td>

</tr>

<tr>

<td>Kasir</td>

<td>

{{ $pembayaran->user->name ?? '-' }}

</td>

</tr>

</table>

</div>

</div>

<div class="clear"></div>

</div>

</div>

<table class="rincian">

<thead>

<tr>

<th width="8%">

NO

</th>

<th width="42%">

KETERANGAN

</th>

<th width="18%">

NOMINAL

</th>

<th width="15%">

BIAYA ADMIN

</th>

<th width="17%">

TOTAL

</th>

</tr>

</thead>

<tbody>

<tr>

<td class="text-center">

1

</td>

<td>

<strong>

Tagihan Internet

</strong>

<br>

<span class="small">

{{ $pembayaran->tagihan->pelanggan->paket->nama_paket }}

</span>

</td>

<td class="text-right">

Rp {{ number_format($pembayaran->nominal,0,',','.') }}

</td>

<td class="text-right">

Rp {{ number_format($pembayaran->biaya_admin,0,',','.') }}

</td>

<td class="text-right total">

Rp {{ number_format($pembayaran->total_bayar,0,',','.') }}

</td>

</tr>

</tbody>

</table>

<table class="summary">

<tr>

<td>

TOTAL TAGIHAN

</td>

<td class="text-right total">

Rp {{ number_format($pembayaran->total_bayar,0,',','.') }}

</td>

</tr>

<tr>

<td>

DIBAYAR

</td>

<td class="text-right total">

Rp {{ number_format($pembayaran->dibayar,0,',','.') }}

</td>

</tr>

<tr>

<td>

KEMBALIAN

</td>

<td class="text-right green">

Rp {{ number_format($pembayaran->kembalian,0,',','.') }}

</td>

</tr>

<tr>

<td>

KETERANGAN

</td>

<td class="text-right">

{{ $pembayaran->keterangan ?: '-' }}

</td>

</tr>

</table>
<div class="footer">

    <div class="footer-left">

        <table style="width:100%;border-collapse:collapse;">

            <tr>

                <td width="110">

                    @if(file_exists(public_path('images/qrcode.png')))

                        <img
                            src="{{ public_path('images/qrcode.png') }}"
                            class="qr">

                    @else

                        <div class="qr" style="text-align:center;line-height:85px;">

                            QR

                        </div>

                    @endif

                </td>

                <td class="note">

                    <strong>

                        Terima kasih telah melakukan pembayaran.

                    </strong>

                    <br><br>

                    Invoice ini dibuat secara otomatis oleh

                    <strong>

                        GNS NETWORK Billing System

                    </strong>

                    <br><br>

                    Dokumen ini sah tanpa tanda tangan

                    dan stempel.

                </td>

            </tr>

        </table>

    </div>

    <div class="signature">

        Hormat Kami,

        <br><br><br><br><br>

        <hr>

        <strong>

            {{ $pembayaran->user->name ?? 'Administrator' }}

        </strong>

        <br>

        Administrator GNS Network

    </div>

    <div class="clear"></div>

</div>

<div class="bottom">

<table style="width:100%;border-collapse:collapse;">

<tr>

<td width="33%">

<b>Website</b><br>

www.gns.net

</td>

<td width="34%" class="text-center">

<b>Email</b><br>

info@gns.net

</td>

<td width="33%" class="text-right">

<b>Dicetak :</b><br>

{{ now()->format('d-m-Y H:i:s') }}

</td>

</tr>

</table>

<br>

<hr>

<div class="text-center">

Copyright © {{ date('Y') }}

<b>GNS NETWORK</b>

All Rights Reserved.

</div>

</div>

</body>

</html>
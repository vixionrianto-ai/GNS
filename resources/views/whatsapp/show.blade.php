@extends('adminlte::page')

@section('title', 'Detail Log WhatsApp')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">
            <i class="fas fa-comment-dots text-success"></i>
            Detail Log WhatsApp
        </h1>

        <small class="text-muted">
            Informasi lengkap pengiriman WhatsApp.
        </small>

    </div>

    <a href="{{ route('whatsapp.index') }}" class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>

        Kembali

    </a>

</div>

@stop


@section('content')

<div class="row">

    <div class="col-lg-8">

        <div class="card card-success card-outline">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-info-circle"></i>

                    Informasi Pengiriman

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="220">Tanggal</th>
                        <td>
                            {{ optional($log->sent_at)->format('d-m-Y H:i:s') ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <th>Pelanggan</th>
                        <td>{{ $log->pelanggan->nama ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Invoice</th>
                        <td>{{ $log->tagihan->invoice_no ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Nomor WhatsApp</th>
                        <td>{{ $log->nomor }}</td>
                    </tr>

                    <tr>
                        <th>Provider</th>
                        <td>{{ strtoupper($log->provider) }}</td>
                    </tr>

                    <tr>
                        <th>Jenis</th>
                        <td>

                            @switch($log->jenis)

                                @case('tagihan')
                                    <span class="badge badge-primary">Tagihan</span>
                                    @break

                                @case('h7')
                                    <span class="badge badge-warning">H-7</span>
                                    @break

                                @case('h3')
                                    <span class="badge badge-info">H-3</span>
                                    @break

                                @case('isolir')
                                    <span class="badge badge-danger">Isolir</span>
                                    @break

                                @case('pembayaran')
                                    <span class="badge badge-success">Pembayaran</span>
                                    @break

                                @default
                                    <span class="badge badge-secondary">
                                        {{ $log->jenis }}
                                    </span>

                            @endswitch

                        </td>
                    </tr>

                    <tr>

                        <th>Status</th>

                        <td>

                            @switch($log->status)

                                @case('success')

                                    <span class="badge badge-success">

                                        <i class="fas fa-check"></i>

                                        Berhasil

                                    </span>

                                    @break

                                @case('failed')

                                    <span class="badge badge-danger">

                                        <i class="fas fa-times"></i>

                                        Gagal

                                    </span>

                                    @break

                                @case('pending')

                                    <span class="badge badge-warning">

                                        <i class="fas fa-clock"></i>

                                        Pending

                                    </span>

                                    @break

                                @default

                                    <span class="badge badge-secondary">

                                        {{ $log->status }}

                                    </span>

                            @endswitch

                        </td>

                    </tr>

                </table>

            </div>

        </div>


        <div class="card card-outline card-primary">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fab fa-whatsapp"></i>

                    Isi Pesan

                </h3>

            </div>

            <div class="card-body">

<pre class="mb-0" style="white-space:pre-wrap;font-size:14px;">{{ $log->pesan }}</pre>

            </div>

        </div>


        <div class="card card-outline card-dark">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-server"></i>

                    Response Provider

                </h3>

            </div>

            <div class="card-body">
            @php
    $response = null;

    if (!empty($log->response)) {
        $response = json_decode($log->response, true);
    }

    $provider = $response['json'] ?? null;
@endphp

@if($provider)

<table class="table table-bordered table-sm">

    <tr>
        <th width="180">HTTP Status</th>
        <td>{{ $response['http_status'] ?? '-' }}</td>
    </tr>

    <tr>
        <th>Status API</th>
        <td>

            @if(($provider['status'] ?? false) === true)

                <span class="badge badge-success">

                    Success

                </span>

            @else

                <span class="badge badge-danger">

                    Failed

                </span>

            @endif

        </td>

    </tr>

    <tr>

        <th>Pesan</th>

        <td>

            {{ $provider['detail'] ?? '-' }}

        </td>

    </tr>

    <tr>

        <th>Process</th>

        <td>

            {{ $provider['process'] ?? '-' }}

        </td>

    </tr>

    <tr>

        <th>Request ID</th>

        <td>

            {{ $provider['requestid'] ?? '-' }}

        </td>

    </tr>

    <tr>

        <th>Target</th>

        <td>

            {{ implode(', ', $provider['target'] ?? []) }}

        </td>

    </tr>

</table>

<hr>

<details>

    <summary style="cursor:pointer">

        Lihat JSON Lengkap

    </summary>

<pre class="mt-3 bg-light border rounded p-3" style="max-height:350px;overflow:auto;">{{ json_encode($provider, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>

</details>

@else

<div class="alert alert-warning mb-0">

Tidak ada response dari provider.

</div>

@endif

            </div>

        </div>

    </div>


    <div class="col-lg-4">

        <div class="card card-outline card-success">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-cogs"></i>

                    Aksi

                </h3>

            </div>

            <div class="card-body">

                <a href="{{ route('whatsapp.index') }}"
                   class="btn btn-secondary btn-block mb-2">

                    <i class="fas fa-arrow-left"></i>

                    Kembali

                </a>

                @if($log->status=='failed')

                    <button
                        class="btn btn-danger btn-block"
                        disabled>

                        <i class="fas fa-redo"></i>

                        Retry (Coming Soon)

                    </button>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection
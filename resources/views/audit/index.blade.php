@extends('adminlte::page')

@section('title', 'Audit Trail')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-history text-primary"></i>

            Audit Trail

        </h1>

        <small class="text-muted">

            Riwayat seluruh aktivitas sistem GNS Enterprise

        </small>

    </div>

    <a href="{{ route('dashboard') }}"
       class="btn btn-outline-secondary">

        <i class="fas fa-arrow-left"></i>

        Dashboard

    </a>

</div>

@stop

@section('content')

<div class="card shadow">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-filter"></i>

            Filter Data

        </h3>

    </div>

    <div class="card-body">

        <form method="GET">

            <div class="row">

                <div class="col-md-3">

                    <label>Module</label>

                    <select
                        name="module"
                        class="form-control">

                        <option value="">

                            Semua Module

                        </option>

                        @foreach($modules as $module)

                            <option
                                value="{{ $module }}"
                                @selected(request('module')==$module)>

                                {{ $module }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <label>Action</label>

                    <select
                        name="action"
                        class="form-control">

                        <option value="">

                            Semua Action

                        </option>

                        @foreach($actions as $action)

                            <option
                                value="{{ $action }}"
                                @selected(request('action')==$action)>

                                {{ ucfirst($action) }}

                            </option>

                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <label>Tanggal</label>

                    <input
                        type="date"
                        name="tanggal"
                        value="{{ request('tanggal') }}"
                        class="form-control">

                </div>

                <div class="col-md-3">

                    <label>&nbsp;</label>

                    <button
                        class="btn btn-primary btn-block">

                        <i class="fas fa-search"></i>

                        Filter

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
<div class="card shadow">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-list"></i>

            Riwayat Aktivitas

        </h3>

    </div>

    <div class="card-body table-responsive p-0">

        <table class="table table-hover table-striped mb-0">

            <thead>

                <tr>

                    <th width="170">Waktu</th>

                    <th width="170">User</th>

                    <th width="120">Module</th>

                    <th width="120">Action</th>

                    <th>Deskripsi</th>

                    <th width="140">IP Address</th>

                </tr>

            </thead>

            <tbody>

                @forelse($audits as $audit)

                    <tr>

                        <td>

                            {{ $audit->created_at->format('d M Y') }}

                            <br>

                            <small class="text-muted">

                                {{ $audit->created_at->format('H:i:s') }}

                            </small>

                        </td>

                        <td>

                            <strong>

                                {{ $audit->user->name ?? 'System' }}

                            </strong>

                        </td>

                        <td>

                            <span class="badge badge-primary">

                                {{ $audit->module }}

                            </span>

                        </td>

                        <td>

                            <span
                                class="badge badge-{{ $audit->badgeColor() }}">

                                {{ strtoupper($audit->action) }}

                            </span>

                        </td>

                        <td>

                            {{ $audit->description }}

                        </td>

                        <td>

                            <small>

                                {{ $audit->ip_address ?? '-' }}

                            </small>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            class="text-center text-muted py-5">

                            <i class="fas fa-history fa-3x mb-3"></i>

                            <br>

                            Belum ada aktivitas.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>
{{-- Detail Properties --}}
<div class="card shadow">

    <div class="card-header">

        <h3 class="card-title">

            <i class="fas fa-code"></i>

            Detail Aktivitas

        </h3>

    </div>

    <div class="card-body p-0">

        <div class="accordion" id="auditAccordion">

            @foreach($audits as $audit)

                @if(!empty($audit->properties))

                <div class="card rounded-0 border-left-0 border-right-0">

                    <div class="card-header" id="heading{{ $audit->id }}">

                        <div class="d-flex justify-content-between align-items-center">

                            <button
                                class="btn btn-link text-left p-0"
                                type="button"
                                data-toggle="collapse"
                                data-target="#collapse{{ $audit->id }}">

                                <strong>

                                    {{ $audit->module }}

                                </strong>

                                -

                                {{ $audit->description }}

                            </button>

                            <small class="text-muted">

                                {{ $audit->created_at->format('d M Y H:i:s') }}

                            </small>

                        </div>

                    </div>

                    <div
                        id="collapse{{ $audit->id }}"
                        class="collapse"
                        data-parent="#auditAccordion">

                        <div class="card-body bg-light">

<pre class="mb-0">{{ json_encode($audit->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

                        </div>

                    </div>

                </div>

                @endif

            @endforeach

        </div>

    </div>

</div>

{{-- Pagination --}}

<div class="d-flex justify-content-end mt-3">

    {{ $audits->links() }}

</div>

@stop
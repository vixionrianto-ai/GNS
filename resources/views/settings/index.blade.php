@extends('adminlte::page')

@section('title', 'Pengaturan')

@section('content_header')
    <h1>
        <i class="fas fa-cogs"></i>
        Pengaturan Sistem
    </h1>
@stop

@section('content')

<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form
        action="{{ route('settings.update') }}"
        method="POST">

        @csrf
        <div class="card card-primary card-outline">

    <div class="card-header">

        <h3 class="card-title">

            Konfigurasi GNS

        </h3>

    </div>

    <div class="card-body">
        <ul class="nav nav-tabs" id="settingTab" role="tablist">

    <li class="nav-item">
        <a class="nav-link active"
           data-toggle="tab"
           href="#billing">
            Billing
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link"
           data-toggle="tab"
           href="#whatsapp">
            WhatsApp
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link"
           data-toggle="tab"
           href="#mikrotik">
            MikroTik
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link"
           data-toggle="tab"
           href="#invoice">
            Invoice
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link"
           data-toggle="tab"
           href="#system">
            Sistem
        </a>
    </li>

</ul>
<div class="tab-content mt-4">

    <div class="tab-pane fade show active" id="billing">

        @foreach($settings['billing'] ?? [] as $setting)

            <div class="form-group row">

                <label class="col-md-4 col-form-label">

                    {{ $setting->description }}

                </label>

                <div class="col-md-8">

                    <x-setting-input :setting="$setting" />

                </div>

            </div>

        @endforeach

    </div>

    <div class="tab-pane fade" id="whatsapp">

        @foreach($settings['whatsapp'] ?? [] as $setting)

            <div class="form-group row">

                <label class="col-md-4 col-form-label">

                    {{ $setting->description }}

                </label>

                <div class="col-md-8">

                    <x-setting-input :setting="$setting" />

                </div>

            </div>

            
        @endforeach

    </div>

    <div class="tab-pane fade" id="mikrotik">

        @foreach($settings['mikrotik'] ?? [] as $setting)

            <div class="form-group row">

                <label class="col-md-4 col-form-label">

                    {{ $setting->description }}

                </label>

                <div class="col-md-8">

                    <x-setting-input :setting="$setting" />

                </div>

            </div>

        @endforeach

    </div>

    <div class="tab-pane fade" id="invoice">

        @foreach($settings['invoice'] ?? [] as $setting)

            <div class="form-group row">

                <label class="col-md-4 col-form-label">

                    {{ $setting->description }}

                </label>

                <div class="col-md-8">

                    <x-setting-input :setting="$setting" />

                </div>

            </div>

        @endforeach

    </div>

    <div class="tab-pane fade" id="system">

        @foreach($settings['system'] ?? [] as $setting)

            <div class="form-group row">

                <label class="col-md-4 col-form-label">

                    {{ $setting->description }}

                </label>

                <div class="col-md-8">

                    <x-setting-input :setting="$setting" />

                </div>

            </div>

        @endforeach

    </div>

</div>
</div>

<div class="card-footer text-right">

    <a
        href="{{ route('settings.index') }}"
        class="btn btn-secondary">

        <i class="fas fa-undo"></i>

        Reset

    </a>

    <button
        class="btn btn-primary">

        <i class="fas fa-save"></i>

        Simpan Pengaturan

    </button>

</div>

</div>

</form>

</div>

@stop
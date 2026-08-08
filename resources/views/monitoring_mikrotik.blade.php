@extends('adminlte::page')

@section('title', 'Monitoring MikroTik')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0">
            <i class="fas fa-network-wired text-primary"></i>
            Monitoring MikroTik
        </h1>
        <small class="text-muted">
            Enterprise Router Monitoring
        </small>
    </div>

    <div>
        @if($mikrotikStatus)
            <span class="badge badge-success px-3 py-2">
                <i class="fas fa-circle"></i>
                ONLINE
            </span>
        @else
            <span class="badge badge-danger px-3 py-2">
                <i class="fas fa-circle"></i>
                OFFLINE
            </span>
        @endif
    </div>
</div>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-info">

            <div class="inner">

                <h3>{{ $routerIdentity }}</h3>

                <p>Router Identity</p>

            </div>

            <div class="icon">
                <i class="fas fa-server"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-success">

            <div class="inner">

                <h3>{{ $routerVersion }}</h3>

                <p>RouterOS Version</p>

            </div>

            <div class="icon">
                <i class="fas fa-microchip"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-warning">

            <div class="inner">

                <h3>{{ $pppActive }}</h3>

                <p>PPP Active</p>

            </div>

            <div class="icon">
                <i class="fas fa-users"></i>
            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-danger">

            <div class="inner">

                <h3>{{ $pppSecret }}</h3>

                <p>Total PPP Secret</p>

            </div>

            <div class="icon">
                <i class="fas fa-key"></i>
            </div>

        </div>

    </div>

</div>

<div class="row">

    <div class="col-lg-6">

        <div class="card card-primary">

            <div class="card-header">

                <h3 class="card-title">
                    Informasi Router
                </h3>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <tr>
                        <th width="35%">Identity</th>
                        <td>{{ $routerIdentity }}</td>
                    </tr>

                    <tr>
                        <th>RouterOS</th>
                        <td>{{ $routerVersion }}</td>
                    </tr>

                    <tr>
                        <th>Uptime</th>
                        <td>{{ $routerUptime }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>

                            @if($mikrotikStatus)
                                <span class="badge badge-success">
                                    Online
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    Offline
                                </span>
                            @endif

                        </td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

    <div class="col-lg-6">

        <div class="card card-success">

            <div class="card-header">

                <h3 class="card-title">
                    Resource Router
                </h3>

            </div>

            <div class="card-body">

                <p class="mb-1">
                    CPU Load
                </p>

                <div class="progress mb-4">

                    <div class="progress-bar bg-primary"
                         style="width: {{ $routerCpu }}%">

                        {{ $routerCpu }}%

                    </div>

                </div>

                <hr>

                <h5>Free Memory</h5>

                <h3>

                    {{ $routerMemory }}

                </h3>

            </div>

        </div>

    </div>

</div>

@stop
<div class="col-lg-8">

        <div class="card shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="fas fa-chart-area text-success"></i>
                    Grafik Pendapatan
                </h5>

            </div>

            <div class="card-body">

                <canvas id="pendapatanChart" height="110"></canvas>

            </div>

        </div>

    </div>
    <div class="col-lg-4">

    <div class="card shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">
                <i class="fas fa-chart-pie text-primary"></i>
                Status Tagihan
            </h5>

        </div>

        <div class="card-body">

            <canvas id="statusChart" height="220"></canvas>

        </div>

    </div>

</div>

@section('js')

<script>

const ctx = document.getElementById('pendapatanChart');

if(ctx){

new Chart(ctx,{

type:'line',

data:{

labels:@json($chartLabels),

datasets:[{

    label:'Pendapatan',

    data:@json($chartData),

    borderColor:'#28a745',

    borderWidth:3,

    fill:false,

    tension:0.3

}]

},

options:{

responsive:true,

maintainAspectRatio:false

}

});

}

const statusCtx = document.getElementById('statusChart');

if(statusCtx){

new Chart(statusCtx,{

type:'doughnut',

data:{

labels:[
'Lunas',
'Sebagian',
'Belum Bayar',
'Jatuh Tempo'
],

datasets:[{

    data:@json($statusChart),

    backgroundColor:[
        '#28a745',
        '#ffc107',
        '#6c757d',
        '#dc3545'
    ],

    borderColor:[
        '#28a745',
        '#ffc107',
        '#6c757d',
        '#dc3545'
    ],

    borderWidth:1

}]

},

options:{

responsive:true,

plugins:{

legend:{

position:'bottom'

}

}

}

});

}

</script>

@stop
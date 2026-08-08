<div class="card kpi-card h-100">
    <div class="card-body">

        <div class="kpi-header">

            <div>
                <div class="kpi-title">{{ $title }}</div>

                <div class="{{ $money ?? false ? 'kpi-money' : 'kpi-value' }}">
                    {{ $value }}
                </div>

                @isset($description)
                    <div class="kpi-desc text-muted">
                        {{ $description }}
                    </div>
                @endisset
            </div>

            <div class="kpi-icon bg-{{ $color }}">
                <i class="{{ $icon }}"></i>
            </div>

        </div>

        @isset($progress)
            <div class="progress progress-thin mt-3">
                <div
                    class="progress-bar bg-{{ $color }}"
                    style="width: {{ $progress }}%">
                </div>
            </div>
        @endisset

    </div>
</div>
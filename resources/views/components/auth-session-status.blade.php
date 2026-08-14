@props(['status'])

@if ($status)
    <div {{ $attributes->merge([
        'class' => 'alert alert-success mb-3'
    ]) }}>
        {{ $status }}
    </div>
@endif
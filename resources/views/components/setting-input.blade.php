@switch($setting->type)

    @case('integer')

        <input
            type="number"
            name="settings[{{ $setting->id }}]"
            class="form-control"
            value="{{ old('settings.'.$setting->id, $setting->value) }}">
        @break


    @case('boolean')

        <select
            name="settings[{{ $setting->id }}]"
            class="form-control">

            <option
                value="true"
                {{ $setting->value == 'true' ? 'selected' : '' }}>
                Aktif
            </option>

            <option
                value="false"
                {{ $setting->value == 'false' ? 'selected' : '' }}>
                Non Aktif
            </option>

        </select>

        @break


    @case('time')

        <input
            type="time"
            name="settings[{{ $setting->id }}]"
            class="form-control"
            value="{{ old('settings.'.$setting->id, $setting->value) }}">
        @break


    @case('string')

        @if($setting->key == 'whatsapp.provider')

            <select
                name="settings[{{ $setting->id }}]"
                class="form-control">

                <option value="fonnte"
                    {{ $setting->value == 'fonnte' ? 'selected' : '' }}>
                    Fonnte
                </option>

                <option value="wablas"
                    {{ $setting->value == 'wablas' ? 'selected' : '' }}>
                    Wablas
                </option>

                <option value="ultramsg"
                    {{ $setting->value == 'ultramsg' ? 'selected' : '' }}>
                    UltraMsg
                </option>

            </select>

        @else

            <input
                type="text"
                name="settings[{{ $setting->id }}]"
                class="form-control"
                value="{{ old('settings.'.$setting->id, $setting->value) }}">

        @endif

        @break

    @case('textarea')

    @php
        $isWhatsappTemplate = str_starts_with($setting->key, 'whatsapp.template');
    @endphp

    <textarea
        name="settings[{{ $setting->id }}]"
        class="form-control {{ $isWhatsappTemplate ? 'font-monospace' : '' }}"
        rows="{{ $isWhatsappTemplate ? 14 : 8 }}"
        style="{{ $isWhatsappTemplate ? 'resize:vertical;' : '' }}">{{ old('settings.'.$setting->id, $setting->value) }}</textarea>

    @if($isWhatsappTemplate)

    <small class="text-muted">

    Gunakan placeholder seperti:

    <strong>{nama}</strong>,
    <strong>{invoice}</strong>,
    <strong>{periode}</strong>,
    <strong>{jatuh_tempo}</strong>,
    <strong>{total}</strong>,
    <strong>{total_sisa}</strong>,
    <strong>{isp}</strong>

    </small>

    @endif

    @break
    
    @default

        <input
            type="text"
            name="settings[{{ $setting->id }}]"
            class="form-control"
            value="{{ old('settings.'.$setting->id, $setting->value) }}">

@endswitch
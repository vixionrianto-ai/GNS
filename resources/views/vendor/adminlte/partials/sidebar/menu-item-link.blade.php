<li @isset($item['id']) id="{{ $item['id'] }}" @endisset class="nav-item">

    @if(isset($item['method']) && strtolower($item['method']) === 'post')

        <form method="POST"
              action="{{ $item['href'] }}"
              style="display:block;">
            @csrf

            <button type="submit"
                    class="nav-link {{ $item['class'] ?? '' }} border-0 bg-transparent w-100 text-left">

                <i class="nav-icon {{ $item['icon'] ?? 'far fa-fw fa-circle' }}"></i>

                <p>{{ $item['text'] }}</p>

            </button>
        </form>

    @else

        <a class="nav-link {{ $item['class'] ?? '' }} @isset($item['shift']) {{ $item['shift'] }} @endisset"
           href="{{ $item['href'] }}"
           @isset($item['target']) target="{{ $item['target'] }}" @endisset
           {!! $item['data-compiled'] ?? '' !!}>

            <i class="nav-icon {{ $item['icon'] ?? 'far fa-fw fa-circle' }}"></i>

            <p>
                {{ $item['text'] }}

                @isset($item['label'])
                    <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                        {{ $item['label'] }}
                    </span>
                @endisset
            </p>

        </a>

    @endif

</li>
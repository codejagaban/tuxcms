@php
    $rows = [['phone', 'Phone'], ['email', 'Email'], ['address', 'Address'], ['hours', 'Hours']];
@endphp
<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
        </div>
        <dl class="reach">
            @foreach ($rows as [$key, $label])
                @continue(blank(data_get($d, $key)))
                <div>
                    <dt>{{ $label }}</dt>
                    <dd>
                        @if ($key === 'email')
                            <a href="mailto:{{ data_get($d, $key) }}">{{ data_get($d, $key) }}</a>
                        @elseif ($key === 'phone')
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($d, $key)) }}">{{ data_get($d, $key) }}</a>
                        @else
                            {!! nl2br(e(data_get($d, $key))) !!}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

@php
    $rows = [['email', 'Email'], ['phone', 'Phone'], ['address', 'Address'], ['hours', 'Hours']];
@endphp
<section class="block" @tuxsection($section)>
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
        </div>
        <dl class="reach">
            @foreach ($rows as [$key, $label])
                @continue(blank(data_get($d, $key)))
                <div @class(['reach__row', 'reach__row--big' => in_array($key, ['email', 'phone'], true)])>
                    <dt>{{ $label }}</dt>
                    {{-- A link carries its own marker; otherwise the line itself does. --}}
                    <dd @field($section, in_array($key, ['email', 'phone'], true) ? '' : "data.{$key}")>
                        @if ($key === 'email')
                            <a href="mailto:{{ data_get($d, $key) }}" @field($section, "data.{$key}")>{{ data_get($d, $key) }}</a>
                        @elseif ($key === 'phone')
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($d, $key)) }}" @field($section, "data.{$key}")>{{ data_get($d, $key) }}</a>
                        @else
                            {!! nl2br(e(data_get($d, $key))) !!}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

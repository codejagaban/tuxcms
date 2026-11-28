@php
    $rows = [['email', 'Email', 'mail'], ['phone', 'Phone', 'phone'], ['address', 'Address', 'pin'], ['hours', 'Hours', 'clock']];
@endphp
<section class="block" @tuxsection($section)>
    <div class="wrap split">
        <div class="side-head">
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled(data_get($d, 'eyebrow')))
                <p class="lede lede--strong" @field($section, 'data.eyebrow')>{{ data_get($d, 'eyebrow') }}</p>
            @endif
            @if (filled($section->content))
                <div class="lede prose" @field($section, 'content')>
                    @include('site.themes.shopfront.partials.paragraphs', ['text' => $section->content])
                </div>
            @endif
            @if (filled(data_get($d, 'image')))
                <figure class="frame frame--side">
                    <img src="{{ data_get($d, 'image') }}" alt="{{ data_get($d, 'image_alt', '') }}" loading="lazy" @image($section, 'data.image')>
                </figure>
            @endif
        </div>
        <dl class="reach">
            @foreach ($rows as [$key, $label, $glyph])
                @continue(blank(data_get($d, $key)))
                <div @class(['reach__row', 'reach__row--big' => in_array($key, ['email', 'phone'], true)]) style="--hue: var(--{{ ['email' => 'blue', 'phone' => 'green', 'address' => 'rose', 'hours' => 'amber'][$key] }})">
                    <dt>@include('site.themes.shopfront.partials.glyph', ['name' => $glyph, 'class' => 'reach__glyph']){{ $label }}</dt>
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

@php
    $rows = [['email', 'Email', 'mail'], ['phone', 'Phone', 'phone'], ['address', 'Address', 'pin'], ['hours', 'Hours', 'clock']];
    $shown = collect($rows)->filter(fn ($row) => filled(data_get($d, $row[0])));
@endphp
{{-- The benefit cards of the Automation SaaS template: a card to each way
     of getting in touch, the icon in a soft square at the top and the detail
     at the foot; a photograph, when there is one, as a tall card at the end. --}}
<section class="block" @tuxsection($section)>
    <div class="wrap">
        <div class="row-head">
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
        </div>
        <div @class(['ways', 'ways--pictured' => filled(data_get($d, 'image'))])>
            <dl class="ways__list">
                @foreach ($shown as [$key, $label, $glyph])
                    <div class="way" style="--hue: var(--{{ ['email' => 'blue', 'phone' => 'green', 'address' => 'rose', 'hours' => 'amber'][$key] }})">
                        <span class="way__tile">@include('site.themes.shopfront.partials.glyph', ['name' => $glyph, 'class' => 'way__glyph'])</span>
                        <dt class="way__label">{{ $label }}</dt>
                        {{-- A link carries its own marker; otherwise the line itself does. --}}
                        <dd class="way__value" @field($section, in_array($key, ['email', 'phone'], true) ? '' : "data.{$key}")>
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
            @if (filled(data_get($d, 'image')))
                <figure class="ways__photo">
                    <img src="{{ data_get($d, 'image') }}" alt="{{ data_get($d, 'image_alt', '') }}" loading="lazy" @image($section, 'data.image')>
                </figure>
            @endif
        </div>
    </div>
</section>

@php
    $items = array_values(array_filter(data_get($d, 'items', []), fn ($item) => filled(data_get($item, 'name'))));
    // The ring wants about twenty stops to look full. A short list goes
    // round it more than once; only the first pass is read out or editable.
    $passes = count($items) ? max(1, (int) ceil(20 / count($items))) : 0;
    $stops = count($items) * $passes;
@endphp
@if (count($items))
{{-- The integrations block of the Automation SaaS template: the heading,
     a line and a button in the middle, inside a great ring whose lower arc
     carries the services' marks slowly round beneath them. The ring turns
     in CSS, so with no script it still turns; it holds still for visitors
     who ask for less motion, and in the dashboard's preview. --}}
<section class="block orbit" @tuxsection($section) style="--stops: {{ $stops }}">
    <div class="wrap orbit__in">
        @include('site.themes.shopfront.partials.head')
        @if (filled(data_get($d, 'cta_label')) && filled(data_get($d, 'url')))
            <a class="button button--quiet orbit__go" href="{{ data_get($d, 'url') }}"><span @field($section, 'data.cta_label')>{{ data_get($d, 'cta_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
        @endif
        <ul class="sr-only">
            @foreach ($items as $i => $item)
                <li @field($section, "data.items.{$i}.name")>{{ data_get($item, 'name') }}</li>
            @endforeach
        </ul>
    </div>
    <div class="orbit__frame" aria-hidden="true"><div class="orbit__ring">
        @for ($n = 0; $n < $stops; $n++)
            @php
                $i = $n % count($items);
                $item = $items[$i];
                $logo = data_get($item, 'logo');
                $url = data_get($item, 'url');
            @endphp
            <div class="orbit__stop" style="--at: {{ $n }}">
                <{{ filled($url) ? 'a' : 'span' }} class="orbit__mark" @if (filled($url)) href="{{ $url }}" tabindex="-1" @endif title="{{ data_get($item, 'name') }}">
                    @if (filled($logo))
                        <img src="{{ $logo }}" alt="" loading="lazy" @if ($n === $i) @image($section, "data.items.{$i}.logo") @endif>
                    @else
                        <span class="orbit__initial">{{ mb_substr(data_get($item, 'name'), 0, 1) }}</span>
                    @endif
                </{{ filled($url) ? 'a' : 'span' }}>
            </div>
        @endfor
    </div></div>
</section>
@endif

@php
    $items = data_get($d, 'items', []);
    // With photographs the list becomes a wall of work; without, a trade list.
    $pictured = collect($items)->contains(fn ($item) => filled(data_get($item, 'image')));
@endphp
@if (count($items) && $pictured)
<section class="block" @tuxsection($section)>
    <div class="wrap">
        <div class="split works__head">
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="lede" @field($section, 'content')>{{ $section->content }}</p>
            @endif
        </div>
        <ul class="works">
            @foreach ($items as $i => $item)
                @php $url = data_get($item, 'url'); @endphp
                <li class="work">
                    @if (filled(data_get($item, 'image')))
                        <figure class="frame">
                            <img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" loading="lazy" @image($section, "data.items.{$i}.image")>
                        </figure>
                    @endif
                    <h3 class="work__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                    @if (filled(data_get($item, 'description')))
                        <p class="work__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                    @endif
                    @if (filled($url) && filled(data_get($item, 'link_label')))
                        <a class="quiet work__go" href="{{ $url }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@elseif (count($items))
<section class="block" @tuxsection($section)>
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="lede" @field($section, 'content')>{{ $section->content }}</p>
            @endif
        </div>
        <ul @class(['trades', 'trades--single' => count($items) < 4])>
            @foreach ($items as $i => $item)
                <li class="trade">
                    @include('site.partials.icon', ['name' => data_get($item, 'icon', 'box'), 'class' => ''])
                    <h3 class="trade__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                    @if (filled(data_get($item, 'description')))
                        <p class="trade__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                    @endif
                    @if (filled(data_get($item, 'url')) && filled(data_get($item, 'link_label')))
                        <a class="quiet trade__what" href="{{ data_get($item, 'url') }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

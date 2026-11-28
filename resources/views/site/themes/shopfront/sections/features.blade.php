@php
    $items = data_get($d, 'items', []);
    // With photographs the list becomes a wall; without, a bento of cards.
    $pictured = collect($items)->contains(fn ($item) => filled(data_get($item, 'image')));
@endphp
@if (count($items))
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @if (filled($section->title) || filled($section->content))
            <div class="row-head">
                @if (filled($section->title))
                    <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
                @endif
                @if (filled($section->content))
                    <p class="lede" @field($section, 'content')>{{ $section->content }}</p>
                @endif
            </div>
        @endif

        @if ($pictured)
            <ul class="wall">
                @foreach ($items as $i => $item)
                    @php $url = data_get($item, 'url'); @endphp
                    <li class="wall__item">
                        @if (filled(data_get($item, 'image')))
                            <figure class="frame">
                                <img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" loading="lazy" @image($section, "data.items.{$i}.image")>
                            </figure>
                        @endif
                        <h3 class="wall__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                        @if (filled(data_get($item, 'description')))
                            <p class="wall__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                        @endif
                        @if (filled($url) && filled(data_get($item, 'link_label')))
                            <a class="link" href="{{ $url }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            {{-- A bento of cards, wide and narrow in turn, each with a piece of
                 the product at its foot. An odd card out takes the full row. --}}
            @php $spans = [5, 7, 7, 5]; @endphp
            <ul class="bento">
                @foreach ($items as $i => $item)
                    @php $span = ($i === count($items) - 1 && $i % 2 === 0) ? 12 : $spans[$i % 4]; @endphp
                    <li class="card card--{{ $span }}">
                        <div class="card__text">
                            @include('site.themes.shopfront.partials.glyph', ['name' => data_get($item, 'icon', 'box'), 'class' => 'card__mark'])
                            <h3 class="card__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                            @if (filled(data_get($item, 'description')))
                                <p class="card__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                            @endif
                            @if (filled(data_get($item, 'url')) && filled(data_get($item, 'link_label')))
                                <a class="link card__go" href="{{ data_get($item, 'url') }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                            @endif
                        </div>
                        @include('site.themes.shopfront.partials.fragment', ['item' => $item])
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endif

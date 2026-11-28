@php
    $items = data_get($d, 'items', []);
    // With photographs the list becomes a wall; without, a grid of panels.
    $pictured = collect($items)->contains(fn ($item) => filled(data_get($item, 'image')));
    $columns = (int) data_get($d, 'columns', 0);
    $columns = in_array($columns, [2, 3, 4], true) ? $columns : (count($items) % 3 === 0 ? 3 : 2);
    $columns = min($columns, max(1, count($items)));
    // The dashboard's own marker colours, taken in turn.
    $hues = ['blue', 'green', 'amber', 'rose'];
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
            <ul class="tiles tiles--{{ $columns }}">
                @foreach ($items as $i => $item)
                    <li class="tile tile--{{ $hues[$i % count($hues)] }}">
                        @include('site.partials.icon', ['name' => data_get($item, 'icon', 'box'), 'class' => 'tile__mark'])
                        <h3 class="tile__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                        @if (filled(data_get($item, 'description')))
                            <p class="tile__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                        @endif
                        @if (filled(data_get($item, 'url')) && filled(data_get($item, 'link_label')))
                            <a class="link tile__go" href="{{ data_get($item, 'url') }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endif

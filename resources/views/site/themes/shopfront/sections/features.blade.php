@php
    $items = data_get($d, 'items', []);
    // The editor's columns choose the layout: one makes the key-features
    // grid, a wide card over a row; two, or none chosen, a bento; three or
    // four, even rows.
    $columns = (int) data_get($d, 'columns', 2);
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

        @if ($columns === 1)
            {{-- The grid of Time Tracking's "Key features", from the template
                 pack: the first feature as a wide card, its words on the left
                 and its piece of the product rising from the foot on the right;
                 the rest as cards in a row, each with its piece in a panel on
                 top, then its name, a line, and a pill to read more. --}}
            @php $rest = array_slice($items, 1, null, true); @endphp
            <div class="keys">
                @foreach ($items as $i => $item)
                    @php
                        $url = data_get($item, 'url');
                        $span = $i === 0 ? 12 : (count($rest) === 1 ? 12 : (count($rest) === 2 ? 6 : 4));
                    @endphp
                    <article @class(['key', 'key--wide' => $i === 0]) style="--span: {{ $span }}; --hue: var(--{{ ['blue', 'green', 'amber', 'rose'][$i % 4] }})">
                        <div class="key__visual">
                            @if (filled(data_get($item, 'image')))
                                <figure class="bit bit--photo">
                                    <img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" loading="lazy" @image($section, "data.items.{$i}.image")>
                                </figure>
                            @else
                                @include('site.themes.shopfront.partials.fragment', ['item' => $item])
                            @endif
                        </div>
                        <div class="key__text">
                            @include('site.themes.shopfront.partials.glyph', ['name' => data_get($item, 'icon', 'box'), 'class' => 'key__glyph'])
                            <h3 class="key__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                            @if (filled(data_get($item, 'description')))
                                <p class="key__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                            @endif
                            @if (filled($url) && filled(data_get($item, 'link_label')))
                                <a class="button button--quiet button--small key__go" href="{{ $url }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span></a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            {{-- A bento of cards, wide and narrow in turn, each with a piece of
                 the product at its foot. An odd card out takes the full row. --}}
            @php
                $even = in_array($columns, [3, 4], true) ? intdiv(12, $columns) : null;
                $spans = [5, 7, 7, 5];
            @endphp
            <ul class="bento">
                @foreach ($items as $i => $item)
                    @php $span = $even ?? (($i === count($items) - 1 && $i % 2 === 0) ? 12 : $spans[$i % 4]); @endphp
                    <li class="card card--{{ $span }}" style="--hue: var(--{{ ['blue', 'green', 'amber', 'rose'][$i % 4] }})">
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
                        @if (filled(data_get($item, 'image')))
                            <figure class="bit bit--photo">
                                <img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" loading="lazy" @image($section, "data.items.{$i}.image")>
                            </figure>
                        @else
                            @include('site.themes.shopfront.partials.fragment', ['item' => $item])
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endif

@php
    $items = data_get($d, 'items', []);
    // With photographs the list becomes a tour; without, a bento of cards.
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
            {{-- A tour: a card to a feature, its photograph on one side and its
                 words on the other. As the page scrolls, each card holds at the
                 top and the next slides up over it, while the rail beside them
                 marks the one in view. The stacking is plain CSS, so every card
                 is there with no script at all. --}}
            <div class="tour" data-tour>
                <ol class="tour__cards">
                    @foreach ($items as $i => $item)
                        @php $url = data_get($item, 'url'); @endphp
                        <li class="tour__card" id="tour-{{ $section->id }}-{{ $i + 1 }}" style="--n: {{ $i }}; --hue: var(--{{ ['blue', 'green', 'amber', 'rose'][$i % 4] }})" data-tour-card>
                            <div class="tour__text">
                                <p class="tour__step"><span class="data">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>@include('site.themes.shopfront.partials.glyph', ['name' => data_get($item, 'icon', 'box'), 'class' => 'tour__glyph'])</p>
                                <h3 class="tour__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</h3>
                                @if (filled(data_get($item, 'description')))
                                    <p class="tour__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                                @endif
                                @if (filled($url) && filled(data_get($item, 'link_label')))
                                    <a class="button button--quiet tour__go" href="{{ $url }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                                @endif
                            </div>
                            @if (filled(data_get($item, 'image')))
                                <figure class="tour__photo">
                                    <img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" loading="lazy" @image($section, "data.items.{$i}.image")>
                                </figure>
                            @else
                                @include('site.themes.shopfront.partials.fragment', ['item' => $item])
                            @endif
                        </li>
                    @endforeach
                </ol>
                <nav class="tour__rail" aria-label="{{ $section->title ?: 'Features' }}">
                    @foreach ($items as $i => $item)
                        <a href="#tour-{{ $section->id }}-{{ $i + 1 }}" @class(['is-here' => $i === 0]) data-tour-step aria-label="{{ data_get($item, 'title') }}"><span class="data">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span></a>
                    @endforeach
                </nav>
            </div>
        @else
            {{-- A bento of cards, wide and narrow in turn, each with a piece of
                 the product at its foot. An odd card out takes the full row. --}}
            @php
                // Two columns, or none chosen, makes the bento; three or four
                // make even rows of that many.
                $columns = (int) data_get($d, 'columns', 2);
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
                        @include('site.themes.shopfront.partials.fragment', ['item' => $item])
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
@endif

@php
    $items = data_get($d, 'items', []);
    // The editor's columns choose the layout: one makes a showcase, a list
    // beside a panel; two, or none chosen, a bento; three or four, even rows.
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
            {{-- A showcase: the features listed down one side, and the one
                 chosen open in a large panel beside them, as a piece of the
                 product, or its photograph if it has one. It moves on to the
                 next by itself, a line under the tab showing how long until it
                 does, and holds still while it is pointed at or used. Every
                 feature's words are always in the list; only the pictures
                 take turns, and the first shows with no script at all. --}}
            <div class="show" data-show>
                <ol class="show__list" role="tablist" aria-label="{{ $section->title ?: 'Features' }}">
                    @foreach ($items as $i => $item)
                        @php $url = data_get($item, 'url'); @endphp
                        <li @class(['show__item', 'is-on' => $i === 0]) style="--hue: var(--{{ ['blue', 'green', 'amber', 'rose'][$i % 4] }})">
                            <h3 class="show__head">
                                <button type="button" class="show__tab" role="tab" id="show-{{ $section->id }}-tab-{{ $i }}" aria-controls="show-{{ $section->id }}-panel-{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}" data-show-tab>
                                    @include('site.themes.shopfront.partials.glyph', ['name' => data_get($item, 'icon', 'box'), 'class' => 'show__glyph'])
                                    <span class="show__name" @field($section, "data.items.{$i}.title")>{{ data_get($item, 'title') }}</span>
                                </button>
                            </h3>
                            <div class="show__more">
                                @if (filled(data_get($item, 'description')))
                                    <p class="show__what" @field($section, "data.items.{$i}.description")>{{ data_get($item, 'description') }}</p>
                                @endif
                                @if (filled($url) && filled(data_get($item, 'link_label')))
                                    <a class="link" href="{{ $url }}"><span @field($section, "data.items.{$i}.link_label")>{{ data_get($item, 'link_label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                                @endif
                            </div>
                            <span class="show__time" aria-hidden="true"></span>
                        </li>
                    @endforeach
                </ol>
                <div class="show__stage">
                    @foreach ($items as $i => $item)
                        <div class="show__panel" role="tabpanel" id="show-{{ $section->id }}-panel-{{ $i }}" aria-labelledby="show-{{ $section->id }}-tab-{{ $i }}" @if ($i > 0) hidden @endif data-show-panel>
                            @if (filled(data_get($item, 'image')))
                                <figure class="bit bit--photo">
                                    <img src="{{ data_get($item, 'image') }}" alt="{{ data_get($item, 'title') }}" loading="lazy" @image($section, "data.items.{$i}.image")>
                                </figure>
                            @else
                                @include('site.themes.shopfront.partials.fragment', ['item' => $item])
                            @endif
                        </div>
                    @endforeach
                </div>
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

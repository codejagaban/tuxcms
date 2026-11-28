@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block tally" @tuxsection($section)>
    <div class="wrap">
        @include('site.themes.shopfront.partials.head')
        <dl class="tally__list tally__list--{{ min(4, count($items)) }}">
            @foreach ($items as $i => $item)
                {{-- As the dashboard's Overview counts: a label with its marker
                     colour, then the figure. --}}
                <div class="tally__item" style="--hue: var(--{{ ['blue', 'green', 'amber', 'rose'][$i % 4] }})">
                    <dt class="tally__of"><span class="tally__dot" aria-hidden="true"></span><span @field($section, "data.items.{$i}.label")>{{ data_get($item, 'label') }}</span></dt>
                    <dd class="tally__n" @field($section, "data.items.{$i}.value")>{{ data_get($item, 'value') }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
@endif

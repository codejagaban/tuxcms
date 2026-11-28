@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @include('site.themes.shopfront.partials.head')
        <div @class(['words', 'words--one' => count($items) === 1])>
            @foreach ($items as $i => $item)
                <figure class="word">
                    <blockquote @field($section, "data.items.{$i}.quote")>{{ data_get($item, 'quote') }}</blockquote>
                    <figcaption>
                        <span class="word__who" @field($section, "data.items.{$i}.author")>{{ data_get($item, 'author') }}</span>
                        @if (filled(data_get($item, 'role')))
                            <span class="word__role" @field($section, "data.items.{$i}.role")>{{ data_get($item, 'role') }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

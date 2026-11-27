@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @if (filled($section->title))
            <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
        @endif
        <div class="words">
            @foreach ($items as $i => $item)
                <figure class="word">
                    <blockquote @field($section, "data.items.{$i}.quote")>{{ data_get($item, 'quote') }}</blockquote>
                    <figcaption>
                        <div class="word__who" @field($section, "data.items.{$i}.author")>{{ data_get($item, 'author') }}</div>
                        @if (filled(data_get($item, 'role')))
                            <div class="word__role" @field($section, "data.items.{$i}.role")>{{ data_get($item, 'role') }}</div>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

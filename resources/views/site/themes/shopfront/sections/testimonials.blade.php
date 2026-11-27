@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="block">
    <div class="wrap">
        @if (filled($section->title))
            <h2 class="display head">{{ $section->title }}</h2>
        @endif
        <div class="words">
            @foreach ($items as $item)
                <figure class="word">
                    <blockquote>{{ data_get($item, 'quote') }}</blockquote>
                    <figcaption>
                        <div class="word__who">{{ data_get($item, 'author') }}</div>
                        @if (filled(data_get($item, 'role')))
                            <div class="word__role">{{ data_get($item, 'role') }}</div>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

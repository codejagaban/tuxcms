@php $image = data_get($d, 'image'); @endphp
<section class="block">
    <div class="wrap split">
        <div>
            @if (filled($section->title))
                <h2 class="display head">{{ $section->title }}</h2>
            @endif
            @if (filled(data_get($d, 'eyebrow')))
                <p class="lede">{{ data_get($d, 'eyebrow') }}</p>
            @endif
        </div>
        <div>
            <div class="prose">
                @include('site.themes.shopfront.partials.paragraphs', ['text' => $section->content])
            </div>
            @if (filled($image))
                <figure class="frame frame--wide frame--after">
                    <img src="{{ $image }}" alt="{{ data_get($d, 'image_alt') }}" loading="lazy">
                </figure>
            @endif
        </div>
    </div>
</section>

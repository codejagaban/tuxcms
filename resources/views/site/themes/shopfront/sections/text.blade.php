@php $image = data_get($d, 'image'); @endphp
{{-- After "The team behind smarter automation" in the Automation SaaS
     template: with a photograph, a tall picture on the left and the words
     beside it; without one, the heading and the words side by side. --}}
<section class="block" @tuxsection($section)>
    <div @class(['wrap', 'story', 'story--pictured' => filled($image)])>
        @if (filled($image))
            <figure class="story__photo">
                <img src="{{ $image }}" alt="{{ data_get($d, 'image_alt') }}" loading="lazy" @image($section, 'data.image')>
            </figure>
        @endif
        <div class="story__head">
            @if (filled($section->title))
                <h2 class="display head" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled(data_get($d, 'eyebrow')))
                <p class="lede" @field($section, 'data.eyebrow')>{{ data_get($d, 'eyebrow') }}</p>
            @endif
        </div>
        <div class="story__words prose" @field($section, 'content')>
            @include('site.themes.shopfront.partials.paragraphs', ['text' => $section->content])
        </div>
    </div>
</section>

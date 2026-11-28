@php
    $front = $page->is_homepage ?? false;
    $centred = ! $front && data_get($d, 'alignment') === 'center';
    $photo = data_get($d, 'background_image');
    $primary = data_get($d, 'primary_cta');
    $secondary = data_get($d, 'secondary_cta');
    // The longer the title, the smaller it is set, so it holds to two lines.
    $length = mb_strlen(trim((string) $section->title));
    $scale = $length <= 16 ? 'short' : ($length <= 36 ? 'medium' : 'long');
    $hasAside = filled(data_get($d, 'subheading')) || filled($section->content) || filled(data_get($primary, 'label')) || filled(data_get($secondary, 'label'));
@endphp
<section @class(['hero', 'hero--front' => $front, 'hero--page' => ! $front, 'hero--centred' => $centred]) @tuxsection($section)>
    @if ($front)
        {{-- A meadow under a moving sky, behind the title and the editor. Its
             foot melts into the page. Decorative, so it is hidden from
             assistive technology; the still frame stands in until it plays,
             and for anyone who asks for less motion. --}}
        <div class="hero__sky" aria-hidden="true">
            <video class="hero__film" src="/themes/shopfront/media/meadow.mp4" poster="/themes/shopfront/media/meadow.jpg" muted loop playsinline preload="metadata"></video>
        </div>
    @else
        @include('site.themes.shopfront.partials.sky')
    @endif
    <div class="wrap">
        <div class="hero__grid">
            @if (filled($section->title))
                <h1 class="display hero__title hero__title--{{ $scale }}" @field($section, 'title')>{{ $section->title }}</h1>
            @endif

            @if ($hasAside)
                <div class="hero__aside">
                    @if (filled(data_get($d, 'subheading')))
                        <p class="hero__sub" @field($section, 'data.subheading')>{{ data_get($d, 'subheading') }}</p>
                    @endif
                    @if (filled($section->content))
                        <div class="hero__say prose" @field($section, 'content')>
                            @include('site.themes.shopfront.partials.paragraphs', ['text' => $section->content])
                        </div>
                    @endif
                    @if (filled(data_get($primary, 'label')) || filled(data_get($secondary, 'label')))
                        <div class="hero__do">
                            @if (filled(data_get($primary, 'label')))
                                <a href="{{ data_get($primary, 'url', '#') }}" class="button">@include('site.themes.shopfront.partials.chip')<span @field($section, 'data.primary_cta.label')>{{ data_get($primary, 'label') }}</span></a>
                            @endif
                            @if (filled(data_get($secondary, 'label')))
                                <a href="{{ data_get($secondary, 'url', '#') }}" class="link"><span @field($section, 'data.secondary_cta.label')>{{ data_get($secondary, 'label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>

        @if ($front)
            {{-- The front page shows the product itself: a working copy of the
                 editor, holding this very page. --}}
            @include('site.themes.shopfront.partials.studio', ['hero' => $section, 'sections' => $sections ?? collect([$section])])
        @endif

        @if (filled($photo))
            <figure class="frame frame--banner">
                <img src="{{ $photo }}" alt="" @image($section, 'data.background_image')>
            </figure>
        @endif
        @if (filled(data_get($d, 'image')))
            <figure class="frame frame--banner">
                <img src="{{ data_get($d, 'image') }}" alt="{{ data_get($d, 'image_alt', '') }}" @image($section, 'data.image')>
            </figure>
        @endif
    </div>
</section>

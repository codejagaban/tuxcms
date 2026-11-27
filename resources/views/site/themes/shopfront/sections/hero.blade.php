@php
    $front = $page->is_homepage ?? false;
    $left = data_get($d, 'alignment') === 'left';
    $photo = data_get($d, 'background_image');
    $primary = data_get($d, 'primary_cta');
    $secondary = data_get($d, 'secondary_cta');
    // A sign painter sizes the lettering to the board: the longer the line,
    // the smaller it is set, so a title holds to one or two lines.
    $length = mb_strlen(trim((string) $section->title));
    $scale = $length <= 16 ? 'short' : ($length <= 34 ? 'medium' : 'long');
    $hasFooting = filled($section->content) || filled(data_get($primary, 'label')) || filled(data_get($secondary, 'label'));
@endphp
<section @class(['sign', 'sign--front' => $front, 'sign--page' => ! $front, 'sign--left' => $left])>
    @if (filled($photo))
        <div class="sign__photo" style="background-image:url('{{ e($photo) }}')" role="presentation"></div>
    @endif
    <div class="wrap">
        <div @class(['board', 'sign__board', 'board--lit' => $front])>
            @if (filled($section->title))
                <h1 class="display sign__title sign__title--{{ $scale }}">{{ $section->title }}</h1>
            @endif
            @if (filled(data_get($d, 'subheading')))
                <p class="sign__trade">{{ data_get($d, 'subheading') }}</p>
            @endif
        </div>

        @if ($hasFooting)
            <div class="sign__footing">
                <div class="sign__say prose">
                    @include('site.themes.shopfront.partials.paragraphs', ['text' => $section->content])
                </div>
                <div class="sign__do">
                    @if (filled(data_get($primary, 'label')))
                        <a href="{{ data_get($primary, 'url', '#') }}" class="plate"><span>{{ data_get($primary, 'label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
                    @endif
                    @if (filled(data_get($secondary, 'label')))
                        <a href="{{ data_get($secondary, 'url', '#') }}" class="quiet"><span>{{ data_get($secondary, 'label') }}</span></a>
                    @endif
                </div>
            </div>
        @endif

        @if (filled(data_get($d, 'image')))
            <figure class="frame frame--banner">
                <img src="{{ data_get($d, 'image') }}" alt="{{ data_get($d, 'image_alt', '') }}">
            </figure>
        @endif
    </div>
</section>

@php $primary = data_get($d, 'primary_cta'); @endphp
<section class="block call" @tuxsection($section)>
    <div class="wrap">
        {{-- A card of the meadow, so the page ends where it began. --}}
        <div class="call__card">
            @if (filled($section->title))
                <h2 class="display call__title" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="call__say" @field($section, 'content')>{{ $section->content }}</p>
            @endif
            @if (filled(data_get($primary, 'label')))
                <a href="{{ data_get($primary, 'url', '#') }}" class="button">@include('site.themes.shopfront.partials.chip')<span @field($section, 'data.primary_cta.label')>{{ data_get($primary, 'label') }}</span></a>
            @endif
        </div>
    </div>
</section>

@php $primary = data_get($d, 'primary_cta'); @endphp
<section class="block call" @tuxsection($section)>
    <div class="wrap">
        {{-- A card of the meadow, so the page ends where it began, with the
             words on a pane of the dashboard's frosted glass. --}}
        <div class="call__card">
            <div class="call__glass">
            @if (filled($section->title))
                <h2 class="display call__title" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="call__say" @field($section, 'content')>{{ $section->content }}</p>
            @endif
            @if (filled(data_get($primary, 'label')))
                <a href="{{ data_get($primary, 'url', '#') }}" class="button"><span @field($section, 'data.primary_cta.label')>{{ data_get($primary, 'label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
            @endif
            </div>
        </div>
    </div>
</section>

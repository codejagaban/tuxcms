@php $primary = data_get($d, 'primary_cta'); @endphp
<section class="block" @tuxsection($section)>
    <div class="wrap">
        <div class="board hang">
            @if (filled($section->title))
                <h2 class="display hang__title" @field($section, 'title')>{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="hang__say" @field($section, 'content')>{{ $section->content }}</p>
            @endif
            @if (filled(data_get($primary, 'label')))
                <a href="{{ data_get($primary, 'url', '#') }}" class="plate"><span @field($section, 'data.primary_cta.label')>{{ data_get($primary, 'label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
            @endif
        </div>
    </div>
</section>

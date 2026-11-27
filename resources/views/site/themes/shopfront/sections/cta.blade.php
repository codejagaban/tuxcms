@php $primary = data_get($d, 'primary_cta'); @endphp
<section class="block">
    <div class="wrap">
        <div class="board hang">
            @if (filled($section->title))
                <h2 class="display hang__title">{{ $section->title }}</h2>
            @endif
            @if (filled($section->content))
                <p class="hang__say">{{ $section->content }}</p>
            @endif
            @if (filled(data_get($primary, 'label')))
                <a href="{{ data_get($primary, 'url', '#') }}" class="plate"><span>{{ data_get($primary, 'label') }}</span>@include('site.themes.shopfront.partials.arrow')</a>
            @endif
        </div>
    </div>
</section>

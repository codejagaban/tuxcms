@php $primary = data_get($d, 'primary_cta'); @endphp
{{-- The closing card of the Automation SaaS template: a large white card,
     the words and the button centred high in it, and a picture rising from
     its foot. Here the picture is the meadow, so the page ends where it
     began. --}}
<section class="block call" @tuxsection($section)>
    <div class="wrap">
        <div class="call__card">
            <div class="call__words">
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
            <div class="call__meadow" aria-hidden="true"></div>
        </div>
    </div>
</section>

@php
    $lat = (float) data_get($d, 'lat', 0);
    $lng = (float) data_get($d, 'lng', 0);
    $zoom = (int) data_get($d, 'zoom', 14);
    // Span the bounding box from the zoom level so the embed frames sensibly.
    $span = max(0.002, 0.08 / max(1, $zoom - 10));
    $bbox = implode(',', [$lng - $span, $lat - $span / 2, $lng + $span, $lat + $span / 2]);
@endphp
@if ($lat || $lng)
<section class="block">
    <div class="wrap">
        @if (filled($section->title))
            <h2 class="display head head--over">{{ $section->title }}</h2>
        @endif
        {{-- OpenStreetMap needs no API key and sets no third-party cookies. --}}
        <div class="map">
            <iframe title="{{ data_get($d, 'label', 'Map') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.openstreetmap.org/export/embed.html?bbox={{ urlencode($bbox) }}&layer=mapnik&marker={{ $lat }},{{ $lng }}"></iframe>
        </div>
        <div class="map__note">
            @if (filled(data_get($d, 'label')))
                <p>{{ data_get($d, 'label') }}</p>
            @endif
            <a class="quiet" href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map={{ $zoom }}/{{ $lat }}/{{ $lng }}" target="_blank" rel="noopener noreferrer"><span>Open in Maps</span>@include('site.themes.shopfront.partials.arrow')</a>
        </div>
    </div>
</section>
@endif

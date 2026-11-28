@php
    $lat = (float) data_get($d, 'lat', 0);
    $lng = (float) data_get($d, 'lng', 0);
    $zoom = (int) data_get($d, 'zoom', 14);
    // Span the bounding box from the zoom level so the embed frames sensibly.
    $span = max(0.002, 0.08 / max(1, $zoom - 10));
    $bbox = implode(',', [$lng - $span, $lat - $span / 2, $lng + $span, $lat + $span / 2]);
    // With no coordinates, a place to search for still gets a card that
    // opens it in the map.
    $query = trim((string) data_get($d, 'query'));
    $placed = $lat || $lng;
@endphp
@if ($placed || $query !== '')
<section class="block" @tuxsection($section)>
    <div class="wrap">
        @include('site.themes.shopfront.partials.head')
        @if ($placed)
        {{-- OpenStreetMap needs no API key and sets no third-party cookies. --}}
        {{-- As the contact page of the Automation SaaS template: the map in a
             rounded card, the place on a glass pill over it. --}}
        <div class="map">
            @if (filled(data_get($d, 'label')))
                <p class="map__pin" aria-hidden="true">@include('site.themes.shopfront.partials.glyph', ['name' => 'pin']){{ data_get($d, 'label') }}</p>
            @endif
            <iframe title="{{ data_get($d, 'label', 'Map') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                src="https://www.openstreetmap.org/export/embed.html?bbox={{ urlencode($bbox) }}&layer=mapnik&marker={{ $lat }},{{ $lng }}"></iframe>
        </div>
        <div class="map__note">
            @if (filled(data_get($d, 'label')))
                <p @field($section, 'data.label')>{{ data_get($d, 'label') }}</p>
            @endif
            <a class="link" href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map={{ $zoom }}/{{ $lat }}/{{ $lng }}" target="_blank" rel="noopener noreferrer"><span>Open in Maps</span>@include('site.themes.shopfront.partials.arrow')</a>
        </div>
        @else
            <div class="place">
                @include('site.themes.shopfront.partials.glyph', ['name' => 'pin', 'class' => 'place__glyph'])
                <div>
                    @if (filled(data_get($d, 'label')))
                        <p class="place__name" @field($section, 'data.label')>{{ data_get($d, 'label') }}</p>
                    @endif
                    <p class="place__where">{{ $query }}</p>
                </div>
                <a class="button button--quiet" href="https://www.openstreetmap.org/search?query={{ urlencode($query) }}" target="_blank" rel="noopener noreferrer"><span>Open in Maps</span>@include('site.themes.shopfront.partials.arrow')</a>
            </div>
        @endif
    </div>
</section>
@endif

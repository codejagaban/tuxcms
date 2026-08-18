@php
    $lat = (float) data_get($d, 'lat', 0);
    $lng = (float) data_get($d, 'lng', 0);
    $zoom = (int) data_get($d, 'zoom', 14);
    // Span the bounding box from the zoom level so the embed frames sensibly.
    $span = max(0.002, 0.08 / max(1, $zoom - 10));
    $bbox = implode(',', [$lng - $span, $lat - $span / 2, $lng + $span, $lat + $span / 2]);
@endphp
@if ($lat || $lng)
<section class="mx-auto max-w-4xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-6 text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    {{-- OpenStreetMap needs no API key and sets no third-party cookies. --}}
    <div class="overflow-hidden rounded-xl border border-gray-200">
        <iframe
            title="{{ data_get($d, 'label', 'Map') }}"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="h-80 w-full border-0"
            src="https://www.openstreetmap.org/export/embed.html?bbox={{ urlencode($bbox) }}&layer=mapnik&marker={{ $lat }},{{ $lng }}"></iframe>
    </div>
    <p class="mt-3 text-sm text-gray-600">
        {{ data_get($d, 'label') }}
        <a class="ml-2 text-blue-600 hover:underline"
           href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map={{ $zoom }}/{{ $lat }}/{{ $lng }}"
           target="_blank" rel="noopener noreferrer">Open in Maps</a>
    </p>
</section>
@endif

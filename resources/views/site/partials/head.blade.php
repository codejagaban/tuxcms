@php
    use App\Support\Site;

    $meta = $page->getMetaTags();
    $og = $page->getOgTags();
    $twitter = $page->getTwitterTags();
    $schema = $page->getSchemaMarkup();
    $customHead = app(\App\Support\HeadSanitizer::class)->sanitize($page->seo?->custom_head);
    $siteNoindex = Site::isNoindex();
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

@php
    // An explicit meta_title is used verbatim — the editor owns it fully.
    // The site name is only appended when falling back to the page title.
    $explicitTitle = filled($page->seo?->meta_title);
    $title = $explicitTitle
        ? $page->seo->meta_title
        : trim($page->title) . ($page->is_homepage ? '' : ' — ' . Site::name());
@endphp
<title>{{ $title }}</title>

@if (!empty($meta['description']))
    <meta name="description" content="{{ $meta['description'] }}">
@endif
@if (!empty($meta['keywords']))
    <meta name="keywords" content="{{ $meta['keywords'] }}">
@endif

<meta name="robots" content="{{ $siteNoindex ? 'noindex, nofollow' : ($meta['robots'] ?? 'index, follow') }}">
<link rel="canonical" href="{{ $meta['canonical'] }}">

@foreach ($og as $property => $content)
    <meta property="{{ $property }}" content="{{ $content }}">
@endforeach

@foreach ($twitter as $name => $content)
    <meta name="{{ $name }}" content="{{ $content }}">
@endforeach

@if ($verification = \App\Models\Setting::getString('google_site_verification'))
    <meta name="google-site-verification" content="{{ $verification }}">
@endif

<link rel="icon" href="/favicon.ico" sizes="any">

{!! Site::styleTags() !!}

@if (!empty($schema))
    {{-- JSON_HEX_TAG makes a </script> breakout impossible. --}}
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif

@if ($customHead)
    {!! $customHead !!}
@endif

@php
    use App\Support\Site;

    // Google issues two kinds of id. A measurement id ("G-...") is loaded and
    // configured here. A Google tag id ("GTM-...") loads a container that
    // carries its own configuration, so it is loaded and left to do that.
    $analyticsId = trim((string) Site::analyticsId());
    $isContainer = str_starts_with(strtoupper($analyticsId), 'GTM-');
    $scriptUrl = 'https://www.googletagmanager.com/'.($isContainer ? 'gtm.js' : 'gtag/js').'?id='.rawurlencode($analyticsId);
@endphp
@if ($analyticsId && ! Site::isNoindex())
    <script async src="{{ $scriptUrl }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        @unless ($isContainer)
        gtag('config', {!! json_encode($analyticsId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!});
        @endunless
    </script>
@endif

@php $analyticsId = \App\Support\Site::analyticsId(); @endphp
@if ($analyticsId && !\App\Support\Site::isNoindex())
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', {!! json_encode($analyticsId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!});
    </script>
@endif

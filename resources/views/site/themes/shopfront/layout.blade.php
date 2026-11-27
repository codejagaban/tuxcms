@php
    use App\Support\CssMinifier;
    use App\Support\Site;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Pages bring a Page model for the shared head. The careers views have
         none, so they pass a title and description instead. --}}
    @isset($page)
        @include('site.partials.head', ['styleTags' => ''])
    @else
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $headTitle ?? Site::name() }}</title>
        @if (filled($headDescription ?? null))
            <meta name="description" content="{{ $headDescription }}">
        @endif
        <meta name="robots" content="{{ Site::isNoindex() ? 'noindex, nofollow' : 'index, follow' }}">
        <link rel="icon" href="/favicon.ico" sizes="any">
    @endisset
    <link rel="preload" href="/themes/shopfront/fonts/LeMurmure-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ CssMinifier::themeStylesheet('shopfront') }}">
    @stack('head')
</head>
<body>
    <a href="#main" class="skip">Skip to content</a>

    @include('site.themes.shopfront.partials.nav')

    <main id="main">
        @yield('content')
    </main>

    @include('site.themes.shopfront.partials.footer')

    <script src="/themes/shopfront/js/site.js" defer></script>
    @stack('scripts')
    @include('site.partials.analytics')
</body>
</html>

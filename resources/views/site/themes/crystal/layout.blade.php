@php use App\Support\Site; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('site.partials.head')
    <link rel="icon" href="/themes/crystal/images/logo/favicon.png" type="image/png">
    <link rel="icon" href="/themes/crystal/images/logo/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/themes/crystal/css/bootstrap.min.css">
    <link rel="stylesheet" href="/themes/crystal/css/style.css?v=3">
    <link rel="stylesheet" href="/themes/crystal/css/style-responsive.css">
    <link rel="stylesheet" href="/themes/crystal/css/vertical-rhythm.min.css">
    <link rel="stylesheet" href="/themes/crystal/css/magnific-popup.css">
    <link rel="stylesheet" href="/themes/crystal/css/owl.carousel.css">
    <link rel="stylesheet" href="/themes/crystal/css/splitting.css">
    <link rel="stylesheet" href="/themes/crystal/css/YTPlayer.css">
    <link rel="stylesheet" href="/themes/crystal/css/demo-fancy/demo-fancy.css?v=3">
</head>
<body class="appear-animate">
    <div class="page-loader color"><div class="loader">Loading...</div></div>
    <a href="#main" class="btn skip-to-content">Skip to content</a>

    <div class="page" id="top">
        @include('site.themes.crystal.partials.nav')

        <main id="main">
            @yield('content')
        </main>

        @include('site.themes.crystal.partials.footer')
    </div>

    <script src="/themes/crystal/js/jquery.min.js"></script>
    <script src="/themes/crystal/js/bootstrap.bundle.min.js"></script>
    <script src="/themes/crystal/js/plugins.js"></script>
    <script src="/themes/crystal/js/jquery.ajaxchimp.min.js"></script>
    <script src="/themes/crystal/js/contact-form.js"></script>
    <script src="/themes/crystal/js/all.js"></script>
    @include('site.partials.analytics')
</body>
</html>

@php use App\Support\Site; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('site.partials.head')
</head>
<body class="min-h-screen bg-white text-gray-900 antialiased flex flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:shadow">
        Skip to content
    </a>

    @include('site.partials.nav')

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('site.partials.footer')

    @include('site.partials.analytics')
</body>
</html>

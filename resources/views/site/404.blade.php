@extends('site.layout')

@section('content')
    <section class="mx-auto max-w-2xl px-6 py-24 text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">404</p>
        <h1 class="mt-3 text-4xl font-bold text-gray-900">We couldn’t find that page</h1>
        <p class="mt-4 text-gray-600">
            The page you’re looking for may have moved or no longer exists.
        </p>
        <a href="/" class="mt-8 inline-block rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">
            Back to homepage
        </a>
    </section>
@endsection

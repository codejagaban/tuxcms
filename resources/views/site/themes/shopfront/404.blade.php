@extends('site.themes.shopfront.layout')

@section('content')
    <section class="hero hero--page">
        <div class="wrap">
            <div class="hero__grid">
                <h1 class="display hero__title hero__title--medium">Nothing at this address</h1>
                <div class="hero__aside">
                    <div class="hero__say"><p>The page may have moved, or the link may be an old one. Everything here is a step away from the front page.</p></div>
                    <div class="hero__do"><a href="/" class="button">@include('site.themes.shopfront.partials.chip')<span>Back to the front page</span></a></div>
                </div>
            </div>
        </div>
    </section>
@endsection

@extends('site.themes.shopfront.layout')

@section('content')
    <section class="sign sign--page lost">
        <div class="wrap">
            <div class="board sign__board">
                <h1 class="display sign__title">Nothing at this address</h1>
                <p class="sign__trade">The page may have moved, or the link may be an old one.</p>
            </div>
            <div class="sign__footing">
                <p class="sign__say">Everything we have is a step away from the front page.</p>
                <div class="sign__do">
                    <a href="/" class="plate"><span>Back to the front page</span>@include('site.themes.shopfront.partials.arrow')</a>
                </div>
            </div>
        </div>
    </section>
@endsection

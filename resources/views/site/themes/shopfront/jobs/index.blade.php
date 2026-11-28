@php use App\Support\Site; @endphp
@extends('site.themes.shopfront.layout', [
    'headTitle' => 'Careers | ' . Site::name(),
    'headDescription' => 'Current opportunities at ' . Site::name() . '.',
])

@section('content')
    <section class="hero hero--page">
        @include('site.themes.shopfront.partials.sky')
        <div class="wrap">
            <div class="hero__grid">
                <h1 class="display hero__title hero__title--short">Work with us</h1>
                <div class="hero__aside">
                    <div class="hero__say"><p>{{ $jobs->count() === 1 ? 'One role is open' : ($jobs->isEmpty() ? 'No roles are open' : $jobs->count() . ' roles are open') }} at {{ Site::name() }} right now.</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="block">
        <div class="wrap">
            @if ($jobs->isEmpty())
                <p class="notice">There are no open roles at the moment.</p>
            @else
                {{-- The career cards of the Time Tracking template: two to a row,
                     the place and the hours as tags, the title, a line, and a
                     pill to read more. --}}
                <ul class="posts">
                    @foreach ($jobs as $job)
                        <li class="post">
                            <p class="post__tags">
                                @foreach (array_filter([$job->location, $job->job_type]) as $tag)
                                    <span class="post__tag">{{ $tag }}</span>
                                @endforeach
                            </p>
                            <h2 class="post__name">{{ $job->title }}</h2>
                            @if (filled($job->summary))
                                <p class="post__what">{{ $job->summary }}</p>
                            @endif
                            @if (filled($job->salary))
                                <p class="post__fact">@include('site.themes.shopfront.partials.glyph', ['name' => 'coin']){{ $job->salary }}</p>
                            @endif
                            <a class="button button--quiet button--small post__go" href="/careers/{{ $job->slug }}/"><span>Read more</span><span class="sr-only"> about {{ $job->title }}</span></a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection

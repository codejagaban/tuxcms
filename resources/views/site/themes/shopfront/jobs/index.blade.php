@php use App\Support\Site; @endphp
@extends('site.themes.shopfront.layout', [
    'headTitle' => 'Careers | ' . Site::name(),
    'headDescription' => 'Current opportunities at ' . Site::name() . '.',
])

@section('content')
    <section class="sign sign--page">
        <div class="wrap">
            <div class="board sign__board">
                <h1 class="display sign__title">Work with us</h1>
            </div>
        </div>
    </section>

    <section class="block">
        <div class="wrap">
            @if ($jobs->isEmpty())
                <p class="notice">There are no open roles at the moment.</p>
            @else
                <ul class="roles">
                    @foreach ($jobs as $job)
                        <li>
                            <a class="role" href="/careers/{{ $job->slug }}/">
                                <div>
                                    <h2 class="role__title">{{ $job->title }}</h2>
                                    <div class="role__facts">
                                        @foreach (array_filter([$job->location, $job->job_type, $job->salary]) as $fact)
                                            <span>{{ $fact }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="role__go">View role @include('site.themes.shopfront.partials.arrow')</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection

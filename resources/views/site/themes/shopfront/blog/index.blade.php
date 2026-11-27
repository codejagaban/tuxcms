@php
    use App\Support\Site;

    $title = $category ? $category->name : 'Blog';
    $headTitle = ($category ? $category->name.' | Blog' : 'Blog').($pageNumber > 1 ? ' | Page '.$pageNumber : '').' | '.Site::name();
    $description = $category
        ? 'Posts about '.$category->name.' from '.Site::name().'.'
        : 'News and writing from '.Site::name().'.';
@endphp
@extends('site.themes.shopfront.layout', [
    'headTitle' => $headTitle,
    'headDescription' => $description,
])

@push('head')
    <link rel="canonical" href="{{ rtrim(Site::url(), '/') . $path }}">
    <link rel="alternate" type="application/rss+xml" title="{{ Site::name() }} blog" href="/blog/feed.xml">
@endpush

@section('content')
    <section class="sign sign--page">
        <div class="wrap">
            <div class="board sign__board">
                <h1 class="display sign__title">{{ $title }}</h1>
            </div>
        </div>
    </section>

    <section class="block">
        <div class="wrap">
            @if ($categories->isNotEmpty())
                <nav class="shelves" aria-label="Blog categories">
                    <a href="/blog/" @if (! $category) aria-current="page" @endif>All posts</a>
                    @foreach ($categories as $shelf)
                        <a href="/blog/category/{{ $shelf->slug }}/" @if ($category?->id === $shelf->id) aria-current="page" @endif>{{ $shelf->name }}</a>
                    @endforeach
                </nav>
            @endif

            <ul class="works posts">
                @foreach ($posts as $post)
                    <li class="post">
                        <a class="post__link" href="{{ $post->path() }}">
                            @if (filled($post->cover_image))
                                <figure class="frame frame--wide">
                                    <img src="{{ $post->cover_image }}" alt="{{ $post->cover_image_alt }}" loading="lazy" decoding="async">
                                </figure>
                            @endif
                            <p class="post__when">
                                <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>
                                @if ($post->category && ! $category)<span>{{ $post->category->name }}</span>@endif
                            </p>
                            <h2 class="work__name">{{ $post->title }}</h2>
                            <p class="work__what">{{ $post->summary() }}</p>
                            <span class="role__go work__go">Read the post @include('site.themes.shopfront.partials.arrow')</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($lastPage > 1)
                <nav class="turn" aria-label="More posts">
                    <span class="turn__side">
                        @if ($newerUrl)
                            <a class="back" href="{{ $newerUrl }}" rel="prev">@include('site.themes.shopfront.partials.arrow')<span>Newer posts</span></a>
                        @endif
                    </span>
                    <span class="turn__where">Page {{ $pageNumber }} of {{ $lastPage }}</span>
                    <span class="turn__side turn__side--end">
                        @if ($olderUrl)
                            <a class="role__go" href="{{ $olderUrl }}" rel="next">Older posts @include('site.themes.shopfront.partials.arrow')</a>
                        @endif
                    </span>
                </nav>
            @endif
        </div>
    </section>
@endsection

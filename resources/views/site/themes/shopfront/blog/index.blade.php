@php use App\Support\Site; @endphp
@extends('site.themes.shopfront.layout')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ Site::name() }} blog" href="/blog/feed.xml">
@endpush

@section('content')
    <section class="hero hero--page">
        @include('site.themes.shopfront.partials.sky')
        <div class="wrap">
            <div class="hero__grid">
                <h1 class="display hero__title hero__title--{{ mb_strlen($tag?->name ?? 'Blog') <= 16 ? 'short' : 'medium' }}">{{ $tag ? $tag->name : 'Blog' }}</h1>
                <div class="hero__aside">
                    <div class="hero__say"><p>{{ $tag ? 'Posts tagged '.$tag->name.'.' : 'News and writing from '.Site::name().'.' }}@if ($pageNumber > 1) Page {{ $pageNumber }} of {{ $lastPage }}.@endif</p></div>
                </div>
            </div>
        </div>
    </section>

    <section class="block">
        <div class="wrap">
            @if ($tags->isNotEmpty())
                {{-- The tags as the theme's own small pills: the one being
                     shown is solid ink, the rest quiet. --}}
                <nav class="shelf" aria-label="Blog tags">
                    <a class="button button--small {{ $tag ? 'button--quiet' : '' }}" href="/blog/" @if (! $tag) aria-current="page" @endif>All posts</a>
                    @foreach ($tags as $shelf)
                        <a class="button button--small {{ $tag?->id === $shelf->id ? '' : 'button--quiet' }}" href="/blog/tag/{{ $shelf->slug }}/" @if ($tag?->id === $shelf->id) aria-current="page" @endif>{{ $shelf->name }}</a>
                    @endforeach
                </nav>
            @endif

            {{-- The career cards, carrying a post: its cover, its tags on top,
                 the title, the summary, the date and a pill to read it. --}}
            <ul class="posts">
                @foreach ($posts as $post)
                    <li class="post post--story">
                        @if (filled($post->cover_image))
                            <figure class="frame frame--wide post__cover">
                                <img src="{{ $post->cover_image }}" alt="" loading="lazy" decoding="async">
                            </figure>
                        @else
                            {{-- No photograph: the title, set large, holds the
                                 cover's place so the cards in a row stay level. --}}
                            <div class="frame frame--wide post__cover post__cover--type" aria-hidden="true"><span class="display">{{ $post->title }}</span></div>
                        @endif
                        @if ($names = $post->tagNames())
                            <p class="post__tags">
                                @foreach ($names as $name)
                                    <span class="post__tag">{{ $name }}</span>
                                @endforeach
                            </p>
                        @endif
                        <h2 class="post__name">{{ $post->title }}</h2>
                        <p class="post__what">{{ $post->summary() }}</p>
                        <p class="post__fact">@include('site.themes.shopfront.partials.glyph', ['name' => 'calendar'])<time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time></p>
                        <a class="button button--quiet button--small post__go" href="{{ $post->path() }}"><span>Read the post</span><span class="sr-only">: {{ $post->title }}</span></a>
                    </li>
                @endforeach
            </ul>

            @if ($lastPage > 1)
                <nav class="turn" aria-label="More posts">
                    <span>
                        @if ($newerUrl)
                            <a class="back" href="{{ $newerUrl }}" rel="prev">@include('site.themes.shopfront.partials.arrow')<span>Newer posts</span></a>
                        @endif
                    </span>
                    <span class="turn__where">Page {{ $pageNumber }} of {{ $lastPage }}</span>
                    <span class="turn__end">
                        @if ($olderUrl)
                            <a class="button button--quiet button--small" href="{{ $olderUrl }}" rel="next"><span>Older posts</span>@include('site.themes.shopfront.partials.arrow')</a>
                        @endif
                    </span>
                </nav>
            @endif
        </div>
    </section>
@endsection

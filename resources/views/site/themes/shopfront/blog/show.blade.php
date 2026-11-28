@php use App\Support\Site; @endphp
@extends('site.themes.shopfront.layout')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ Site::name() }} blog" href="/blog/feed.xml">
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
    @endif
@endpush

@section('content')
    <section class="hero hero--page">
        @include('site.themes.shopfront.partials.sky')
        <div class="wrap">
            <div class="hero__grid">
                <h1 class="display hero__title hero__title--{{ mb_strlen($post->title) <= 16 ? 'short' : (mb_strlen($post->title) <= 36 ? 'medium' : 'long') }}">{{ $post->title }}</h1>
                <div class="hero__aside">
                    @if (filled($post->excerpt))
                        <div class="hero__say"><p>{{ $post->excerpt }}</p></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <article class="block read">
        <div class="wrap">
            <a class="back" href="/blog/">@include('site.themes.shopfront.partials.arrow')<span>All posts</span></a>

            <div class="read__grid">
                {{-- The facts of the post in the overview card the career
                     pages use, holding its place beside the writing. --}}
                <aside class="brief__card read__card">
                    <dl class="brief__facts">
                        <div><dt>Published</dt><dd><time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time></dd></div>
                        @if ($post->author)
                            <div><dt>Written by</dt><dd>{{ $post->author->name }}</dd></div>
                        @endif
                        <div><dt>Reading time</dt><dd>{{ $post->readingMinutes() }} min</dd></div>
                    </dl>
                    @if ($post->tags->where('type', \App\Models\Post::TAG_TYPE)->isNotEmpty())
                        <p class="post__tags read__tags">
                            @foreach ($post->tags->where('type', \App\Models\Post::TAG_TYPE) as $tag)
                                <a class="post__tag" href="/blog/tag/{{ $tag->slug }}/">{{ $tag->name }}</a>
                            @endforeach
                        </p>
                    @endif
                </aside>

                <div class="read__main">
                    @if (filled($post->cover_image))
                        <figure class="frame frame--wide read__cover">
                            <img src="{{ $post->cover_image }}" alt="{{ $post->cover_image_alt }}" fetchpriority="high" decoding="async">
                        </figure>
                    @endif

                    {{-- Cleaned when it was saved; see App\Support\RichText. --}}
                    <div class="read__body">{!! $post->body !!}</div>
                </div>
            </div>

            @if ($newer || $older)
                <nav class="posts read__onward" aria-label="More posts">
                    @foreach (['Newer post' => $newer, 'Older post' => $older] as $label => $other)
                        @if ($other)
                            <a class="post read__next" href="{{ $other->path() }}">
                                <span class="post__fact">{{ $label }}</span>
                                <span class="post__name">{{ $other->title }}</span>
                            </a>
                        @else
                            <span></span>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>
    </article>
@endsection

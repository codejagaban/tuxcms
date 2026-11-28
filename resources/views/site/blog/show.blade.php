@extends($layout ?? 'site.layout')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ \App\Support\Site::name() }} blog" href="/blog/feed.xml">
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
    @endif
@endpush

@section('content')
    @include('site.blog.partials.style')

    <article class="blog">
        <a class="blog__back" href="/blog/">← All posts</a>

        <h1 class="blog__title">{{ $post->title }}</h1>
        @if (filled($post->excerpt))
            <p class="blog__lead">{{ $post->excerpt }}</p>
        @endif

        <p class="blog__meta">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>
            @if ($post->author)
                · {{ $post->author->name }}
            @endif
            @foreach ($post->tags->where('type', \App\Models\Post::TAG_TYPE) as $tag)
                {{ $loop->first ? '·' : ',' }} <a href="/blog/tag/{{ $tag->slug }}/">{{ $tag->name }}</a>
            @endforeach
            · {{ $post->readingMinutes() }} min read
        </p>

        @if (filled($post->cover_image))
            <figure class="blog__figure">
                <img src="{{ $post->cover_image }}" alt="{{ $post->cover_image_alt }}" fetchpriority="high" decoding="async">
            </figure>
        @endif

        {{-- Cleaned when it was saved; see App\Support\RichText. --}}
        <div class="blog__body">{!! $post->body !!}</div>

        @if ($newer || $older)
            <nav class="blog__onward" aria-label="More posts">
                @foreach (['Newer' => $newer, 'Older' => $older] as $label => $other)
                    @if ($other)
                        <a href="{{ $other->path() }}"><small>{{ $label }}</small><strong>{{ $other->title }}</strong></a>
                    @else
                        <span></span>
                    @endif
                @endforeach
            </nav>
        @endif
    </article>
@endsection

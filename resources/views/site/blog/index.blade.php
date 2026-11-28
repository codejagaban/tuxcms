@extends($layout ?? 'site.layout')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ \App\Support\Site::name() }} blog" href="/blog/feed.xml">
@endpush

@section('content')
    @include('site.blog.partials.style')

    <section class="blog">
        <h1 class="blog__title">{{ $tag ? $tag->name : 'Blog' }}</h1>

        @if ($tags->isNotEmpty())
            <nav aria-label="Blog tags">
                <ul class="blog__tags">
                    <li><a href="/blog/" @if (! $tag) aria-current="page" @endif>All posts</a></li>
                    @foreach ($tags as $shelf)
                        <li><a href="/blog/tag/{{ $shelf->slug }}/" @if ($tag?->id === $shelf->id) aria-current="page" @endif>{{ $shelf->name }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <ol class="blog__list">
            @foreach ($posts as $post)
                <li>
                    <article>
                        @if (filled($post->cover_image))
                            <a href="{{ $post->path() }}" tabindex="-1" aria-hidden="true">
                                <img class="blog__cover" src="{{ $post->cover_image }}" alt="" loading="lazy" decoding="async">
                            </a>
                        @endif
                        <p class="blog__when">
                            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>
                            @if ($names = $post->tagNames())
                                · {{ implode(', ', $names) }}
                            @endif
                        </p>
                        <h2 class="blog__name"><a href="{{ $post->path() }}">{{ $post->title }}</a></h2>
                        <p class="blog__summary">{{ $post->summary() }}</p>
                    </article>
                </li>
            @endforeach
        </ol>

        @if ($lastPage > 1)
            <nav class="blog__turn" aria-label="More posts">
                <span>
                    @if ($newerUrl)
                        <a href="{{ $newerUrl }}" rel="prev">← Newer posts</a>
                    @endif
                </span>
                <span class="blog__turn-where">Page {{ $pageNumber }} of {{ $lastPage }}</span>
                <span>
                    @if ($olderUrl)
                        <a href="{{ $olderUrl }}" rel="next">Older posts →</a>
                    @endif
                </span>
            </nav>
        @endif
    </section>
@endsection

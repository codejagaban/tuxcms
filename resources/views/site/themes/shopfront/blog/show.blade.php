@php
    use App\Support\Site;

    $base = rtrim(Site::url(), '/');
    $url = $base . $post->path();
    $description = $post->meta_description ?: $post->summary(160);
    $image = Site::absolute($post->cover_image) ?: Site::defaultOgImage();

    $article = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->title,
        'description' => $description,
        'image' => $image,
        'datePublished' => $post->published_at?->toAtomString(),
        'dateModified' => $post->updated_at?->toAtomString(),
        'mainEntityOfPage' => $url,
        'articleSection' => $post->category?->name,
        'author' => $post->author
            ? ['@type' => 'Person', 'name' => $post->author->name]
            : ['@type' => 'Organization', 'name' => Site::name()],
        'publisher' => array_filter([
            '@type' => 'Organization',
            'name' => Site::name(),
            'url' => $base,
            'logo' => Site::schemaLogo() ? ['@type' => 'ImageObject', 'url' => Site::schemaLogo()] : null,
        ]),
    ]);
@endphp
@extends('site.themes.shopfront.layout', [
    'headTitle' => ($post->meta_title ?: $post->title) . ' | ' . Site::name(),
    'headDescription' => $description,
])

@push('head')
    <link rel="canonical" href="{{ $url }}">
    <link rel="alternate" type="application/rss+xml" title="{{ Site::name() }} blog" href="/blog/feed.xml">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->meta_title ?: $post->title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:site_name" content="{{ Site::name() }}">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
    @endif
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
    @endif
    {{-- JSON_HEX_TAG makes a </script> breakout impossible. --}}
    <script type="application/ld+json">{!! json_encode($article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <section class="sign sign--page">
        <div class="wrap">
            <div class="board sign__board">
                <h1 class="display sign__title sign__title--post">{{ $post->title }}</h1>
            </div>
            <div class="sign__footing">
                @if (filled($post->excerpt))
                    <p class="sign__say">{{ $post->excerpt }}</p>
                @endif
                <dl class="byline">
                    <div><dt>Published</dt><dd><time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time></dd></div>
                    @if ($post->author)
                        <div><dt>Written by</dt><dd>{{ $post->author->name }}</dd></div>
                    @endif
                    @if ($post->category)
                        <div><dt>Filed under</dt><dd><a href="/blog/category/{{ $post->category->slug }}/">{{ $post->category->name }}</a></dd></div>
                    @endif
                    <div><dt>Reading time</dt><dd>{{ $post->readingMinutes() }} min</dd></div>
                </dl>
            </div>
        </div>
    </section>

    <article class="block">
        <div class="wrap">
            <a class="back" href="/blog/">@include('site.themes.shopfront.partials.arrow')<span>All posts</span></a>

            @if (filled($post->cover_image))
                <figure class="frame frame--banner">
                    <img src="{{ $post->cover_image }}" alt="{{ $post->cover_image_alt }}" fetchpriority="high" decoding="async">
                </figure>
            @endif

            {{-- Cleaned when it was saved; see App\Support\RichText. --}}
            <div class="article">{!! $post->body !!}</div>

            @if ($newer || $older)
                <nav class="onward" aria-label="More posts">
                    @foreach (['Newer' => $newer, 'Older' => $older] as $label => $other)
                        @if ($other)
                            <a class="onward__post" href="{{ $other->path() }}">
                                <span class="onward__label">{{ $label }}</span>
                                <span class="onward__title">{{ $other->title }}</span>
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

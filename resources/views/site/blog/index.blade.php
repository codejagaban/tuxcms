@extends('site.layout')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ \App\Support\Site::name() }} blog" href="/blog/feed.xml">
@endpush

@section('content')
    <section class="mx-auto max-w-3xl px-6 pb-20 pt-14">
        <h1 class="text-4xl font-bold text-gray-900">{{ $category ? $category->name : 'Blog' }}</h1>

        @if ($categories->isNotEmpty())
            <nav class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm" aria-label="Blog categories">
                <a href="/blog/" class="{{ $category ? 'text-gray-600 hover:text-gray-900' : 'font-semibold text-gray-900' }}" @if (! $category) aria-current="page" @endif>All posts</a>
                @foreach ($categories as $shelf)
                    <a href="/blog/category/{{ $shelf->slug }}/" class="{{ $category?->id === $shelf->id ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-900' }}" @if ($category?->id === $shelf->id) aria-current="page" @endif>{{ $shelf->name }}</a>
                @endforeach
            </nav>
        @endif

        <ol class="mt-12 space-y-14">
            @foreach ($posts as $post)
                <li>
                    <article>
                        @if (filled($post->cover_image))
                            <a href="{{ $post->path() }}" tabindex="-1" aria-hidden="true">
                                <img src="{{ $post->cover_image }}" alt="" class="mb-5 aspect-[16/9] w-full rounded-lg object-cover" loading="lazy" decoding="async">
                            </a>
                        @endif
                        <p class="text-sm text-gray-500">
                            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>
                            @if ($post->category && ! $category)
                                · {{ $post->category->name }}
                            @endif
                        </p>
                        <h2 class="mt-2 text-2xl font-bold text-gray-900">
                            <a href="{{ $post->path() }}" class="hover:text-blue-600">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-3 leading-relaxed text-gray-600">{{ $post->summary() }}</p>
                    </article>
                </li>
            @endforeach
        </ol>

        @if ($lastPage > 1)
            <nav class="mt-16 grid grid-cols-3 items-center text-sm" aria-label="More posts">
                <span>
                    @if ($newerUrl)
                        <a href="{{ $newerUrl }}" rel="prev" class="font-medium text-blue-600 hover:text-blue-700">← Newer posts</a>
                    @endif
                </span>
                <span class="text-center text-gray-500">Page {{ $pageNumber }} of {{ $lastPage }}</span>
                <span class="text-right">
                    @if ($olderUrl)
                        <a href="{{ $olderUrl }}" rel="next" class="font-medium text-blue-600 hover:text-blue-700">Older posts →</a>
                    @endif
                </span>
            </nav>
        @endif
    </section>
@endsection

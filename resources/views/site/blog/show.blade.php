@extends('site.layout')

@push('head')
    <link rel="alternate" type="application/rss+xml" title="{{ \App\Support\Site::name() }} blog" href="/blog/feed.xml">
    @if ($post->published_at)
        <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
    @endif
@endpush

@section('content')
    <article class="mx-auto max-w-3xl px-6 pb-20 pt-14">
        <a href="/blog/" class="text-sm font-medium text-blue-600 hover:text-blue-700">← All posts</a>

        <h1 class="mt-6 text-4xl font-bold text-gray-900">{{ $post->title }}</h1>
        @if (filled($post->excerpt))
            <p class="mt-4 text-lg leading-relaxed text-gray-600">{{ $post->excerpt }}</p>
        @endif

        <p class="mt-6 text-sm text-gray-500">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time>
            @if ($post->author)
                · {{ $post->author->name }}
            @endif
            @if ($post->category)
                · <a href="/blog/category/{{ $post->category->slug }}/" class="hover:text-gray-900">{{ $post->category->name }}</a>
            @endif
            · {{ $post->readingMinutes() }} min read
        </p>

        @if (filled($post->cover_image))
            <img src="{{ $post->cover_image }}" alt="{{ $post->cover_image_alt }}" class="mt-10 w-full rounded-lg" fetchpriority="high" decoding="async">
        @endif

        {{-- Cleaned when it was saved; see App\Support\RichText. --}}
        <div class="mt-10 space-y-5 leading-relaxed text-gray-700
            [&_a]:text-blue-600 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-gray-200 [&_blockquote]:pl-5 [&_blockquote]:italic
            [&_code]:rounded [&_code]:bg-gray-100 [&_code]:px-1 [&_code]:text-sm [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-gray-900 [&_pre]:p-4 [&_pre]:text-gray-100 [&_pre_code]:bg-transparent [&_pre_code]:p-0
            [&_h2]:pt-4 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-gray-900 [&_h3]:pt-2 [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:text-gray-900 [&_h4]:font-semibold [&_h4]:text-gray-900
            [&_img]:rounded-lg [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6 [&_hr]:border-gray-200">{!! $post->body !!}</div>

        @if ($newer || $older)
            <nav class="mt-16 grid gap-6 border-t border-gray-200 pt-8 sm:grid-cols-2" aria-label="More posts">
                @foreach (['Newer' => $newer, 'Older' => $older] as $label => $other)
                    @if ($other)
                        <a href="{{ $other->path() }}" class="group {{ $label === 'Older' ? 'sm:text-right' : '' }}">
                            <span class="block text-sm text-gray-500">{{ $label }}</span>
                            <span class="mt-1 block font-semibold text-gray-900 group-hover:text-blue-600">{{ $other->title }}</span>
                        </a>
                    @else
                        <span></span>
                    @endif
                @endforeach
            </nav>
        @endif
    </article>
@endsection

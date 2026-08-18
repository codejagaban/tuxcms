<section class="mx-auto max-w-3xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-4 text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    @if (filled($section->content))
        {{-- Plain text, matching the editor. Never rendered as raw HTML. --}}
        <div class="whitespace-pre-wrap leading-relaxed text-gray-600">{{ $section->content }}</div>
    @endif
</section>

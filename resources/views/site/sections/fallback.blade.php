{{-- Safe renderer for section types with no dedicated partial. --}}
@if (filled($section->title) || filled($section->content))
<section class="mx-auto max-w-3xl px-6 py-10">
    @if (filled($section->title))
        <h2 class="mb-2 text-xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    @if (filled($section->content))
        <div class="whitespace-pre-wrap text-gray-600">{{ $section->content }}</div>
    @endif
</section>
@endif

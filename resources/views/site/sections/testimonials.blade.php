@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="mx-auto max-w-6xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-10 text-center text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    <div class="mx-auto grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">
        @foreach ($items as $item)
            <figure class="rounded-xl border border-gray-200 bg-white p-6">
                <blockquote class="leading-relaxed text-gray-800">{{ data_get($item, 'quote') }}</blockquote>
                <figcaption class="mt-4">
                    <div class="text-sm font-semibold text-gray-900">{{ data_get($item, 'author') }}</div>
                    <div class="text-xs text-gray-500">{{ data_get($item, 'role') }}</div>
                </figcaption>
            </figure>
        @endforeach
    </div>
</section>
@endif

@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="mx-auto max-w-3xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-8 text-center text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    {{-- Native <details> keeps the answers crawlable with zero JavaScript,
         which is what makes the FAQPage schema honest. --}}
    <div class="divide-y divide-gray-200 border-t border-gray-200">
        @foreach ($items as $item)
            <details class="group py-4">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-gray-900">
                    {{ data_get($item, 'question') }}
                    <span class="shrink-0 text-gray-400 transition-transform group-open:rotate-45">+</span>
                </summary>
                <div class="mt-2 whitespace-pre-wrap text-sm text-gray-600">{{ data_get($item, 'answer') }}</div>
            </details>
        @endforeach
    </div>
</section>
@endif

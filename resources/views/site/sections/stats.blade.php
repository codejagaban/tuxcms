@php $items = data_get($d, 'items', []); @endphp
@if (count($items))
<section class="mx-auto max-w-6xl px-6 py-14">
    <div class="rounded-xl bg-slate-50 px-6 py-12">
        @if (filled($section->title))
            <h2 class="mb-10 text-center text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
        @endif
        <div class="mx-auto grid max-w-4xl grid-cols-2 gap-6 text-center md:grid-cols-4">
            @foreach ($items as $item)
                <div>
                    <div class="text-3xl font-bold text-blue-600">{{ data_get($item, 'value') }}</div>
                    <div class="mt-1 text-sm text-gray-600">{{ data_get($item, 'label') }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@php
    $items = data_get($d, 'items', []);
    // Tailwind v4 only emits classes it sees literally, so both variants must
    // appear verbatim here — never assembled by interpolation.
    $cols = (int) data_get($d, 'columns', 3) === 2
        ? 'sm:grid-cols-2'
        : 'sm:grid-cols-2 lg:grid-cols-3';
@endphp
@if (count($items))
<section class="mx-auto max-w-6xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-3 text-center text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    @if (filled($section->content))
        <p class="mx-auto mb-10 max-w-2xl text-center text-gray-600">{{ $section->content }}</p>
    @endif
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 {{ $cols }}">
        @foreach ($items as $item)
            <div class="rounded-xl border border-gray-200 bg-white p-6">
                <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    @include('site.partials.icon', ['name' => data_get($item, 'icon', 'box')])
                </div>
                <h3 class="mb-1 font-semibold text-gray-900">{{ data_get($item, 'title') }}</h3>
                <p class="text-sm text-gray-600">{{ data_get($item, 'description') }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

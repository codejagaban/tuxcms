@php
    // Note: this list lives under `members`, not `items`.
    $members = data_get($d, 'members', []);
    $initials = fn ($name) => collect(explode(' ', trim((string) $name)))
        ->filter()->take(2)->map(fn ($p) => strtoupper($p[0]))->implode('');
@endphp
@if (count($members))
<section class="mx-auto max-w-6xl px-6 py-14">
    @if (filled($section->title))
        <h2 class="mb-10 text-center text-2xl font-bold text-gray-900">{{ $section->title }}</h2>
    @endif
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($members as $member)
            <div class="rounded-xl border border-gray-200 bg-white p-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-700">
                    {{ $initials(data_get($member, 'name')) ?: '?' }}
                </div>
                <div class="font-semibold text-gray-900">{{ data_get($member, 'name') }}</div>
                <div class="mb-2 text-sm text-blue-600">{{ data_get($member, 'role') }}</div>
                <p class="text-sm text-gray-600">{{ data_get($member, 'bio') }}</p>
            </div>
        @endforeach
    </div>
</section>
@endif

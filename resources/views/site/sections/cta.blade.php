@php $primary = data_get($d, 'primary_cta'); @endphp
<section class="mx-auto max-w-6xl px-6 py-16">
    <div class="mx-auto max-w-3xl rounded-2xl bg-slate-900 px-8 py-12 text-center">
        @if (filled($section->title))
            <h2 class="mb-3 text-2xl font-bold text-white">{{ $section->title }}</h2>
        @endif
        @if (filled($section->content))
            <p class="mb-6 text-slate-300">{{ $section->content }}</p>
        @endif
        @if (filled(data_get($primary, 'label')))
            <a href="{{ data_get($primary, 'url', '#') }}"
               class="inline-block rounded-lg bg-blue-600 px-6 py-3 font-medium text-white hover:bg-blue-700">
                {{ data_get($primary, 'label') }}
            </a>
        @endif
    </div>
</section>

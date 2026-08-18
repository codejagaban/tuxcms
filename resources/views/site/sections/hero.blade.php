@php
    $align = data_get($d, 'alignment', 'center');
    $bg = data_get($d, 'background_image');
    $primary = data_get($d, 'primary_cta');
    $secondary = data_get($d, 'secondary_cta');
@endphp
<section class="relative overflow-hidden {{ $bg ? '' : 'bg-gradient-to-br from-slate-50 to-slate-100' }}"
    @if ($bg) style="background-image:url('{{ e($bg) }}');background-size:cover;background-position:center;" @endif>
    <div class="{{ $bg ? 'bg-slate-900/60' : '' }}">
        <div class="mx-auto flex max-w-3xl flex-col gap-4 px-6 py-20 {{ $align === 'left' ? 'items-start text-left' : 'items-center text-center' }}">
            @if (filled(data_get($d, 'subheading')))
                <p class="text-sm font-semibold uppercase tracking-wide {{ $bg ? 'text-blue-200' : 'text-blue-600' }}">
                    {{ data_get($d, 'subheading') }}
                </p>
            @endif

            @if (filled($section->title))
                <h1 class="text-4xl font-bold leading-tight {{ $bg ? 'text-white' : 'text-gray-900' }}">
                    {{ $section->title }}
                </h1>
            @endif

            @if (filled($section->content))
                <p class="text-lg {{ $bg ? 'text-slate-200' : 'text-gray-600' }}">{{ $section->content }}</p>
            @endif

            @if (filled(data_get($primary, 'label')) || filled(data_get($secondary, 'label')))
                <div class="mt-2 flex flex-wrap gap-3 {{ $align === 'left' ? '' : 'justify-center' }}">
                    @if (filled(data_get($primary, 'label')))
                        <a href="{{ data_get($primary, 'url', '#') }}"
                           class="inline-block rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">
                            {{ data_get($primary, 'label') }}
                        </a>
                    @endif
                    @if (filled(data_get($secondary, 'label')))
                        <a href="{{ data_get($secondary, 'url', '#') }}"
                           class="inline-block rounded-lg border px-5 py-2.5 font-medium {{ $bg ? 'border-white/40 text-white' : 'border-gray-300 text-gray-700' }}">
                            {{ data_get($secondary, 'label') }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>

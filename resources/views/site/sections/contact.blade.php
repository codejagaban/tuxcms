@php
    $rows = [
        ['email', 'Email'],
        ['phone', 'Phone'],
        ['address', 'Address'],
        ['hours', 'Hours'],
    ];
@endphp
<section class="mx-auto max-w-3xl px-6 py-14" @tuxsection($section)>
    @if (filled($section->title))
        <h2 class="mb-6 text-2xl font-bold text-gray-900" @field($section, 'title')>{{ $section->title }}</h2>
    @endif
    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        @foreach ($rows as [$key, $label])
            @if (filled(data_get($d, $key)))
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                    {{-- A link carries its own marker; otherwise the value itself does. --}}
                    <dd class="text-gray-800" @field($section, in_array($key, ['email', 'phone'], true) ? '' : "data.{$key}")>
                        @if ($key === 'email')
                            <a href="mailto:{{ data_get($d, $key) }}" class="hover:text-blue-600" @field($section, "data.{$key}")>{{ data_get($d, $key) }}</a>
                        @elseif ($key === 'phone')
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', data_get($d, $key)) }}" class="hover:text-blue-600" @field($section, "data.{$key}")>{{ data_get($d, $key) }}</a>
                        @else
                            {{ data_get($d, $key) }}
                        @endif
                    </dd>
                </div>
            @endif
        @endforeach
    </dl>
</section>

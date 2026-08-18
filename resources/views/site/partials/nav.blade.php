@php use App\Support\Site; @endphp
{{-- Navigation is derived from the page tree — there is no menu builder. --}}
<header class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-4">
        <a href="/" class="flex items-center gap-2 font-semibold text-gray-900">
            @if ($logo = Site::logo())
                <img src="{{ $logo }}" alt="{{ Site::name() }}" class="h-8 w-auto">
            @else
                <span class="text-lg">{{ Site::name() }}</span>
            @endif
        </a>

        @if (!empty($navigation))
            {{-- <details> gives a working mobile disclosure with no JavaScript,
                 which matters on a fully static build. --}}
            <details class="relative md:hidden">
                <summary class="cursor-pointer list-none rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium">
                    Menu
                </summary>
                <nav class="absolute right-0 z-20 mt-2 w-56 rounded-lg border border-gray-200 bg-white py-2 shadow-lg">
                    @foreach ($navigation as $item)
                        <a href="{{ $item['url'] }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            {{ $item['label'] }}
                        </a>
                        @foreach ($item['children'] as $child)
                            <a href="{{ $child['url'] }}" class="block py-2 pl-8 pr-4 text-sm text-gray-500 hover:bg-gray-50">
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    @endforeach
                </nav>
            </details>

            <nav class="hidden items-center gap-1 md:flex">
                @foreach ($navigation as $item)
                    @if (empty($item['children']))
                        <a href="{{ $item['url'] }}"
                           class="rounded-lg px-3 py-2 text-sm font-medium {{ $item['active'] ? 'text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <details class="group relative">
                            <summary class="cursor-pointer list-none rounded-lg px-3 py-2 text-sm font-medium {{ $item['active'] ? 'text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                                {{ $item['label'] }}
                            </summary>
                            <div class="absolute left-0 z-20 mt-1 w-56 rounded-lg border border-gray-200 bg-white py-2 shadow-lg">
                                <a href="{{ $item['url'] }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    {{ $item['label'] }} overview
                                </a>
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @endforeach
            </nav>
        @endif
    </div>
</header>

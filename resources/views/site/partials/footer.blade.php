@php
    use App\Support\Site;
    $contact = Site::contact();
    $social = Site::socialLinks();
@endphp
<footer class="border-t border-gray-200 bg-gray-50">
    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="grid gap-8 md:grid-cols-3">
            <div>
                <div class="font-semibold text-gray-900">{{ Site::name() }}</div>
                @if ($tagline = Site::tagline())
                    <p class="mt-2 text-sm text-gray-600">{{ $tagline }}</p>
                @endif
            </div>

            @if (!empty($navigation))
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Pages</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach ($navigation as $item)
                            <li>
                                <a href="{{ $item['url'] }}" class="text-sm text-gray-600 hover:text-gray-900">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($contact || $social)
                <div>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Get in touch</h2>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        @if (!empty($contact['email']))
                            <li><a href="mailto:{{ $contact['email'] }}" class="hover:text-gray-900">{{ $contact['email'] }}</a></li>
                        @endif
                        @if (!empty($contact['phone']))
                            <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="hover:text-gray-900">{{ $contact['phone'] }}</a></li>
                        @endif
                        @if (!empty($contact['address']))
                            <li>{{ $contact['address'] }}</li>
                        @endif
                    </ul>

                    @if ($social)
                        <ul class="mt-4 flex gap-4 text-sm">
                            @foreach ($social as $network => $url)
                                <li>
                                    <a href="{{ $url }}" rel="noopener noreferrer" target="_blank"
                                       class="capitalize text-gray-600 hover:text-gray-900">{{ $network }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>

        <p class="mt-10 border-t border-gray-200 pt-6 text-xs text-gray-500">
            &copy; {{ $buildYear ?? date('Y') }} {{ Site::name() }}. All rights reserved.
        </p>
    </div>
</footer>

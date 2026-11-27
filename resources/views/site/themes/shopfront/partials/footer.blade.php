@php
    use App\Support\Site;
    $contact = Site::contact();
    $social = Site::socialLinks();
    $legal = Site::legalLinks();
@endphp
<footer class="foot">
    <div class="wrap">
        <div class="foot__grid">
            <p class="foot__line">{{ \App\Models\Setting::getString('footer_intro') ?: (Site::tagline() ?: (Site::description() ?: Site::name())) }}</p>

            @if (! empty($navigation))
                <div>
                    <h2>Pages</h2>
                    <ul>
                        @foreach ($navigation as $item)
                            <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($contact || $social)
                <div>
                    <h2>Get in touch</h2>
                    <ul>
                        @if (! empty($contact['phone']))
                            <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></li>
                        @endif
                        @if (! empty($contact['email']))
                            <li><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
                        @endif
                        @if (! empty($contact['address']))
                            <li class="soft">{{ $contact['address'] }}</li>
                        @endif
                    </ul>
                    @if ($social)
                        <ul class="foot__social">
                            @foreach ($social as $network => $url)
                                <li><a href="{{ $url }}" rel="noopener noreferrer" target="_blank">{{ $network }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>

        <div class="foot__small">
            <p>&copy; {{ $buildYear ?? date('Y') }} {{ Site::name() }}</p>
            @if ($legal)
                <ul>
                    @foreach ($legal as $link)
                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <span class="foot__name" aria-hidden="true">{{ Site::name() }}</span>
</footer>

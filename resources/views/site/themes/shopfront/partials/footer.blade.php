@php
    use App\Support\Site;
    $contact = Site::contact();
    $social = Site::socialLinks();
    $legal = Site::legalLinks();
@endphp
{{-- The footer stands on the dark stage in light and dark alike. --}}
<footer class="foot">
    <div class="wrap">
        <div class="foot__grid">
            <div class="foot__lead">
                <a href="/" class="bar__name foot__name" aria-label="{{ Site::name() }}, front page">@include('site.themes.shopfront.partials.wordmark')</a>
                <p class="foot__line">{{ \App\Models\Setting::getString('footer_intro') ?: (Site::tagline() ?: (Site::description() ?: Site::name())) }}</p>
            </div>

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
                        @if (! empty($contact['email']))
                            <li><a href="mailto:{{ $contact['email'] }}" class="foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'mail'])<span>{{ $contact['email'] }}</span></a></li>
                        @endif
                        @if (! empty($contact['phone']))
                            <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'phone'])<span>{{ $contact['phone'] }}</span></a></li>
                        @endif
                        @if (! empty($contact['address']))
                            <li class="soft foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'pin'])<span>{{ $contact['address'] }}</span></li>
                        @endif
                    </ul>
                    @if ($social)
                        <ul class="foot__social">
                            @foreach ($social as $network => $url)
                                <li><a href="{{ $url }}" rel="noopener noreferrer" target="_blank" aria-label="{{ ucfirst($network === 'twitter' ? 'X' : $network) }}">@include('site.themes.shopfront.partials.social', ['network' => $network])</a></li>
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
</footer>

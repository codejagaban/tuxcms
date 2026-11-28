@php
    use App\Support\Site;
    $contact = Site::contact();
    $social = Site::socialLinks();
    $legal = Site::legalLinks();
    $support = \App\Models\Setting::getString('footer_support_email');
@endphp
{{-- The footer of the Automation SaaS template: on a quiet band, the name,
     a line about the site and its social links on the left; three columns
     of links on the right; and the copyright centred under a hairline. --}}
<footer class="foot">
    <div class="wrap">
        <div class="foot__grid">
            <div class="foot__lead">
                <a href="/" class="bar__name foot__name" aria-label="{{ Site::name() }}, front page">@include('site.themes.shopfront.partials.wordmark')</a>
                <p class="foot__line">{{ \App\Models\Setting::getString('footer_intro') ?: (Site::tagline() ?: (Site::description() ?: Site::name())) }}</p>
                @if ($social)
                    <ul class="foot__social">
                        @foreach ($social as $network => $url)
                            <li><a href="{{ $url }}" rel="noopener noreferrer" target="_blank" aria-label="{{ ucfirst($network === 'twitter' ? 'X' : $network) }}">@include('site.themes.shopfront.partials.social', ['network' => $network])</a></li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="foot__columns">
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

                @if ($contact || filled($support))
                    <div>
                        <h2>Get in touch</h2>
                        <ul>
                            @if (! empty($contact['email']))
                                <li><a href="mailto:{{ $contact['email'] }}" class="foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'mail'])<span>{{ $contact['email'] }}</span></a></li>
                            @endif
                            @if (filled($support))
                                <li><a href="mailto:{{ $support }}" class="foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'mail'])<span>Support: {{ $support }}</span></a></li>
                            @endif
                            @if (! empty($contact['phone']))
                                <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}" class="foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'phone'])<span>{{ $contact['phone'] }}</span></a></li>
                            @endif
                            @if (! empty($contact['address']))
                                <li class="foot__reach">@include('site.themes.shopfront.partials.glyph', ['name' => 'pin'])<span>{{ $contact['address'] }}</span></li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if ($legal)
                    <div>
                        <h2>Legal</h2>
                        <ul>
                            @foreach ($legal as $link)
                                <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <p class="foot__small">&copy; {{ $buildYear ?? date('Y') }} {{ Site::name() }}</p>
    </div>
</footer>

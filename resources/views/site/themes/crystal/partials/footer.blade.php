@php
    use App\Support\Site;
    $contact = Site::contact();
    $social = Site::socialLinks();
@endphp
<footer class="page-section footer bg-dark-1 light-content pb-30">
    <div class="container">
        <div class="row pb-120 pb-sm-80 pb-xs-50">
            <div class="col-lg-4 text-gray mb-md-50">
                <div class="mb-30">
                    <img src="/themes/crystal/images/logo/logo-light.svg" alt="{{ Site::name() }}" width="166" height="50" loading="lazy">
                </div>
                @if (Site::tagline())<p>{{ Site::tagline() }}</p>@endif
                @if (!empty($contact['phone']))
                    <div class="clearlinks"><strong>Tel:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></div>
                @endif
                @if (!empty($contact['email']))
                    <div class="clearlinks"><strong>Email:</strong> <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></div>
                @endif
            </div>

            <div class="col-lg-6 offset-lg-2">
                <div class="row mt-n30">
                    <div class="col-sm-6 mt-30">
                        <h2 class="fw-title">Company</h2>
                        <ul class="fw-menu clearlist">
                            @foreach ($navigation as $item)
                                <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    @if ($social)
                        <div class="col-sm-6 mt-30">
                            <h2 class="fw-title">Social media</h2>
                            <ul class="fw-menu clearlist">
                                @foreach ($social as $network => $url)
                                    <li><a href="{{ $url }}" rel="noopener noreferrer" target="_blank">{{ ucfirst($network) }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row text-gray">
            <div class="col-md-6"><strong>&copy; {{ $buildYear ?? date('Y') }} {{ Site::name() }}</strong></div>
            @if (!empty($contact['address']))<div class="col-md-6 text-md-end">{{ $contact['address'] }}</div>@endif
        </div>
    </div>
</footer>

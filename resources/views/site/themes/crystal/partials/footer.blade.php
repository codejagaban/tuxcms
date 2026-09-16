@php
    use App\Support\Site;

    $footerContact = array_merge([
        'email' => 'info@crystalservicesltd.co.uk',
        'phone' => '+44 771 729 9921',
        'address' => '56 Northfield Street, Worcester, Worcestershire, WR1 1NT.',
    ], Site::contact());
    $footerSocial = Site::socialLinks();
    $legalLinks = Site::legalLinks();
    $footerIntro = \App\Models\Setting::getString(
        'footer_intro',
        'Professional cleaning and salon services for homes, workplaces and people across Worcester.'
    );
    $footerSupportEmail = \App\Models\Setting::getString('footer_support_email', 'support@crystalservicesltd.co.uk');
    $cleaningLinks = Site::linkDirectory('footer_cleaning_links', [
        ['label' => 'Commercial cleaning', 'url' => '/contact/?service=Commercial%20Cleaning#contact_form'],
        ['label' => 'Upholstery cleaning', 'url' => '/contact/?service=Carpet%20%26%20Upholstery%20Cleaning#contact_form'],
        ['label' => 'End-of-tenancy cleaning', 'url' => '/contact/?service=End-of-Tenancy%20Cleaning#contact_form'],
        ['label' => 'Airbnb cleaning', 'url' => '/contact/?service=Airbnb%20Cleaning#contact_form'],
        ['label' => 'Deep cleaning', 'url' => '/contact/?service=Deep%20Cleaning#contact_form'],
        ['label' => 'After-builders cleaning', 'url' => '/contact/?service=After%20Builders%20Cleaning#contact_form'],
    ]);
    $salonLinks = Site::linkDirectory('footer_salon_links', [
        ['label' => 'Braiding', 'url' => '/contact/?service=Braiding#contact_form'],
        ['label' => 'Sew-in', 'url' => '/contact/?service=Sew-in#contact_form'],
        ['label' => 'Dreadlocks', 'url' => '/contact/?service=Dreadlocks#contact_form'],
        ['label' => 'Re-locking', 'url' => '/contact/?service=Re-locking#contact_form'],
        ['label' => 'Relaxing', 'url' => '/contact/?service=Relaxing#contact_form'],
        ['label' => 'Washing & setting', 'url' => '/contact/?service=Washing%20%26%20setting#contact_form'],
        ['label' => 'Natural weaving', 'url' => '/contact/?service=Natural%20weaving#contact_form'],
    ]);
    $socialIcons = [
        'facebook' => 'fa-facebook',
        'twitter' => 'fa-twitter',
        'instagram' => 'fa-instagram',
        'linkedin' => 'fa-linkedin',
        'tiktok' => 'fa-tiktok',
    ];
@endphp
<footer class="page-section footer crystal-footer bg-dark-1 light-content pb-30">
    <div class="container">
        <div class="row crystal-footer-main pb-100 pb-sm-70 pb-xs-50">
            <div class="col-lg-3 text-gray mb-md-50">
                <div class="mb-30">
                    <img src="/themes/crystal/images/logo/logo-light.svg" alt="Crystal Services Limited" width="166" height="50" loading="lazy">
                </div>
                <p class="crystal-footer-intro">{{ $footerIntro }}</p>
                <a class="crystal-footer-enquiry" href="/contact/">Request a quote <span aria-hidden="true">↗</span></a>
            </div>

            <div class="col-lg-9">
                <div class="row mt-n30 crystal-footer-directory">
                    <div class="col-6 col-lg-3 mt-30">
                        <h3 class="fw-title">Company</h3>
                        <ul class="fw-menu clearlist">
                            <li><a href="/">Home</a></li>
                            <li><a href="/about/">About us</a></li>
                            <li><a href="/careers/">Careers</a></li>
                            <li><a href="/contact/">Contact</a></li>
                        </ul>
                    </div>

                    <div class="col-6 col-lg-3 mt-30">
                        <h3 class="fw-title">Cleaning services</h3>
                        <ul class="fw-menu clearlist">
                            @foreach ($cleaningLinks as $link)
                                <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="col-6 col-lg-3 mt-30">
                        <h3 class="fw-title">Salon services</h3>
                        <ul class="fw-menu clearlist">
                            @foreach ($salonLinks as $link)
                                <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="col-6 col-lg-3 mt-30">
                        <h3 class="fw-title">Contact</h3>
                        <address class="crystal-footer-contact">
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $footerContact['phone']) }}">{{ $footerContact['phone'] }}</a>
                            <a href="mailto:{{ $footerContact['email'] }}">{{ $footerContact['email'] }}</a>
                            @if ($footerSupportEmail && $footerContact['email'] !== $footerSupportEmail)
                                <a href="mailto:{{ $footerSupportEmail }}">{{ $footerSupportEmail }}</a>
                            @endif
                        </address>
                        @if ($footerSocial)
                            <h3 class="fw-title crystal-footer-social-title">Follow</h3>
                            <ul class="fw-menu clearlist crystal-footer-social">
                                @foreach ($footerSocial as $network => $url)
                                    <li>
                                        <a href="{{ $url }}" rel="noopener noreferrer" target="_blank">
                                            <i class="{{ $socialIcons[$network] ?? '' }}" aria-hidden="true"></i>
                                            {{ ucfirst($network) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row text-gray crystal-footer-base">
            <div class="col-md-4 col-lg-3"><b>© Crystal Services LTD <span class="date">{{ $buildYear ?? date('Y') }}</span></b></div>
            <div class="col-md-7 offset-md-1 offset-lg-2 clearfix">
                <b>{{ $footerContact['address'] }}</b>
                @if ($legalLinks)
                    <nav class="crystal-footer-legal" aria-label="Legal">
                        @foreach ($legalLinks as $link)
                            <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                        @endforeach
                    </nav>
                @endif
                <div class="local-scroll float-end mt-n20 mt-sm-10">
                    <a href="#top" class="link-to-top"><i class="mi-arrow-up size-24"></i><span class="visually-hidden">Scroll to top</span></a>
                </div>
            </div>
        </div>
    </div>
</footer>

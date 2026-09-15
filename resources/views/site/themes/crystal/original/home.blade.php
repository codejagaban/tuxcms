@php
  $hero = $cmsSections->get('hero');
  $about = $cmsSections->get('about');
  $services = $cmsSections->get('services');
  $salon = $cmsSections->get('salon');
  $decorateHero = static function (string $title): string {
      $escaped = e($title);
      return preg_replace_callback('/crystal/i', static fn ($match) => '<span class="mark-decoration-3-wrap">'.$match[0].'<b class="mark-decoration-3 wow scalexIn" data-wow-delay="1.1s"></b></span>', $escaped);
  };
@endphp
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <title>Crystal Services Limited &mdash; Cleaning &amp; Salon Services in Worcester</title>
    <meta
      name="description"
      content="Professional cleaning and salon hairdressing in Worcester. Commercial, deep and end-of-tenancy cleaning plus braiding, sew-ins, weaves and hair products. Get a free quote today."
    />
    <meta name="keywords" content="cleaning services Worcester, professional cleaning, commercial cleaning, deep cleaning, end of tenancy cleaning, Airbnb cleaning, hairdressing Worcester, braiding, sew-in, Crystal Services Limited" />
    <link rel="canonical" href="{{ rtrim(\App\Support\Site::url(), '/') }}/" />
    <meta name="robots" content="index, follow" />
    <!-- Open Graph Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Crystal Services Limited" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:title" content="Crystal Services Limited &mdash; Cleaning &amp; Salon Services in Worcester" />
    <meta property="og:description" content="Professional cleaning and salon hairdressing in Worcester. Commercial, deep and end-of-tenancy cleaning plus braiding, sew-ins, weaves and hair products. Get a free quote today." />
    <meta property="og:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />
    <meta property="og:url" content="{{ rtrim(\App\Support\Site::url(), '/') }}/" />
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Crystal Services Limited &mdash; Cleaning &amp; Salon Services in Worcester" />
    <meta name="twitter:description" content="Professional cleaning and salon hairdressing in Worcester. Commercial, deep and end-of-tenancy cleaning plus braiding, sew-ins, weaves and hair products. Get a free quote today." />
    <meta name="twitter:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="icon" href="/themes/crystal/images/logo/favicon.png" type="image/png" sizes="any" />
    <link rel="icon" href="/themes/crystal/images/logo/favicon.svg" type="image/svg+xml" />

    <!-- CSS -->
    <link rel="stylesheet" href="/themes/crystal/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/themes/crystal/css/style.css?v=3" />
    <link rel="stylesheet" href="/themes/crystal/css/style-responsive.css" />
    <link rel="stylesheet" href="/themes/crystal/css/vertical-rhythm.min.css" />
    <link rel="stylesheet" href="/themes/crystal/css/magnific-popup.css" />
    <link rel="stylesheet" href="/themes/crystal/css/owl.carousel.css" />
    <link rel="stylesheet" href="/themes/crystal/css/splitting.css" />
    <link rel="stylesheet" href="/themes/crystal/css/YTPlayer.css" />
    <link rel="stylesheet" href="/themes/crystal/css/demo-fancy/demo-fancy.css?v=2" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
      rel="stylesheet" />
      <!-- Structured Data -->
    <script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@graph": [
    {
      "@type": "LocalBusiness",
      "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business",
      "name": "Crystal Services Limited",
      "url": "{{ rtrim(\App\Support\Site::url(), '/') }}/",
      "image": "{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png",
      "logo": "{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/logo/logo.svg",
      "telephone": "+447717299921",
      "email": "info@crystalservicesltd.co.uk",
      "description": "Family-run Worcester business offering professional cleaning services and salon hairdressing.",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "56 Northfield Street",
        "addressLocality": "Worcester",
        "addressRegion": "Worcestershire",
        "postalCode": "WR1 1NT",
        "addressCountry": "GB"
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday"
        ],
        "opens": "09:00",
        "closes": "19:00"
      },
      "areaServed": {
        "@type": "City",
        "name": "Worcester"
      },
      "department": {
        "@type": "HairSalon",
        "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/salon/#salon",
        "name": "Crystal Salon & Hairdressing",
        "url": "{{ rtrim(\App\Support\Site::url(), '/') }}/salon/"
      }
    },
    {
      "@type": "WebSite",
      "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#website",
      "url": "{{ rtrim(\App\Support\Site::url(), '/') }}/",
      "name": "Crystal Services Limited",
      "publisher": {
        "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
      }
    }
  ]
}
    </script>
  </head>

  <body class="appear-animate">
    <!-- Page Loader -->
    <div class="page-loader color">
      <div class="loader">Loading...</div>
    </div>
    <!-- End Page Loader -->

    <!-- Skip to Content -->
    <a href="#main" class="btn skip-to-content">Skip to Content</a>
    <!-- End Skip to Content -->

    <!-- Page Wrap -->
    <div class="page" id="top">
      <!-- Navigation Panel -->
      <nav class="main-nav transparent stick-fixed wow-menubar wch-unset">
        <div class="main-nav-sub container">
          <!-- Logo  (* Add your text or image to the link tag. Use SVG or PNG image format.
                    If you use a PNG logo image, the image resolution must be equal 200% of the visible logo
                    image size for support of retina screens. See details in the template documentation. *) -->
          <div class="nav-logo-wrap position-static local-scroll">
            <a href="/" class="logo">
              <img src="/themes/crystal/images/logo/logo.svg" alt="Crystal Services Limited" width="159" height="48" />
            </a>
          </div>
          <div class="mobile-nav ">
            <div class="flex items-center gap-20">

              <!-- Mobile Menu Button -->
              <div role="button" tabindex="0">
                <i class="mobile-nav-icon"></i>
                <span class="visually-hidden">Menu</span>
              </div>
            </div>
          </div>

          <!-- Main Menu -->
          <div class="inner-nav desktop-nav">
            <ul class="clearlist local-scroll justify-content-end">
              <li><a href="/" class="active">Home</a></li>
              <li><a href="/about/">About</a></li>
              <li><a href="/services/">Services</a></li>
              <li><a href="/salon/">Salon</a></li>
              <li><a href="/contact/">Contact</a></li>
              <li><a href="/careers/">Careers</a></li>

              <li class="desktop-nav-display">
                <div class="vr mt-2"></div>
              </li>
              <li>
                <a href="/contact/" class="opacity-1 no-hover">
                  <span class="btn btn-mod btn-w btn-border-c btn-small btn-round" data-btn-animate="y">
                    Get in touch <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M5 4H9L11 9L8.5 10.5C9.57096 12.6715 11.3285 14.429 13.5 15.5L15 13L20 15V19C20 19.5304 19.7893 20.0391 19.4142 20.4142C19.0391 20.7893 18.5304 21 18 21C14.0993 20.763 10.4202 19.1065 7.65683 16.3432C4.8935 13.5798 3.23705 9.90074 3 6C3 5.46957 3.21071 4.96086 3.58579 4.58579C3.96086 4.21071 4.46957 4 5 4Z"
                        stroke="#4567ED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </span>
                </a>
              </li>
            </ul>
          </div>
          <!-- End Main Menu -->
        </div>
      </nav>
      <!-- End Navigation Panel -->

      <main id="main">
        <!-- Home Section -->
        <section class="home-section bg-gradient-gray-light-2" id="home">
          <!-- Background Shape -->
          <div class="bg-shape-1 wow fadeIn">
            <img src="/themes/crystal/images/demo-fancy/bg-shape-1.svg" alt="" />
          </div>
          <!-- End Background Shape -->

          <div
            class="container position-relative min-height-100vh d-flex align-items-center pt-100 pb-100 pt-sm-120 pb-sm-120">
            <!-- Home Section Content -->
            <div class="home-content text-start">
              <div class="row">
                <!-- Home Section Text -->
                <div
                  class="col-md-10 offset-md-1 col-lg-6 offset-lg-0 col-xl-5 d-flex align-items-center mb-md-60 mb-sm-30">
                  <div class="w-100 text-center text-lg-start">
                    <h2 class="section-caption-fancy mb-30 mb-xs-20 wow fadeInUp" data-wow-duration="1.2s">
                      {{ $hero?->data['subheading'] ?? 'Your Trusted Cleaning Partner' }}
                    </h2>

                    <h1 class="hs-title-10 mb-30">
                      <span class="wow charsAnimIn" data-splitting="chars">
                        {!! $decorateHero($hero?->title ?? 'A crystal cleaning you can trust') !!}
                      </span>
                    </h1>

                    <p class="section-descr mb-40 wow fadeInUp" data-wow-delay="0.6s" data-wow-duration="1.2s"
                      data-wow-offset="0">
                      {{ $hero?->content ?? "We proudly provide unparalleled cleaning services across the UK. Our mission is to consistently exceed our clients' expectations with reliable, high-quality cleaning solutions." }}
                    </p>

                    <div class="local-scroll wow fadeInUp wch-unset" data-wow-delay="0.7s" data-wow-duration="1.2s">
                      <a href="{{ $hero?->data['primary_cta']['url'] ?? '/services/' }}"
                        class="btn btn-mod btn-color btn-large btn-round btn-hover-anim me-1 mb-xs-10">
                        <span>{{ $hero?->data['primary_cta']['label'] ?? 'View Services' }}</span>
                      </a>

                      <a href="{{ $hero?->data['secondary_cta']['url'] ?? '/contact/' }}" class="btn btn-mod btn-border-c btn-large btn-round mb-xs-10">
                        {{ $hero?->data['secondary_cta']['label'] ?? 'Book a cleaning' }}
                      </a>
                    </div>
                  </div>
                </div>
                <!-- End Home Section Text -->

                <!-- Image -->
                <div class="col-lg-6 col-xl-7 d-flex align-items-center">
                  <div class="w-100 wow fadeInLeft" data-wow-delay="0.7s">
                    <div class="position-relative mt-40 mb-20">
                      <img src="{{ $hero?->data['image'] ?? '/themes/crystal/images/intro/hero.png' }}" alt="{{ $hero?->data['image_alt'] ?? 'Professional cleaner from Crystal Services Limited' }}" class="w-100" />

                      <!-- Decorative Waves -->
                      <div class="decoration-5 d-none d-sm-block" data-rellax-y data-rellax-speed="-0.7"
                        data-rellax-percentage="0.5">
                        <img src="/themes/crystal/images/demo-fancy/decoration-1.svg" alt="" loading="lazy" />
                      </div>
                      <!-- End Decorative Waves -->
                    </div>
                  </div>
                </div>
                <!-- End Image -->
              </div>
            </div>
            <!-- End Home Section Content -->

            <!-- Scroll Down -->
            <div class="local-scroll scroll-down-wrap-type-1 wow fadeInUp" data-wow-offset="0">
              <div class="container text-center text-lg-start">
                <a href="#about" class="scroll-down-1">
                  <div class="scroll-down-1-icon">
                    <i class="mi-arrow-down"></i>
                  </div>
                  <div class="scroll-down-1-text">Scroll Down</div>
                </a>
              </div>
            </div>
            <!-- End Scroll Down -->
          </div>
        </section>
        <!-- End Home Section -->

        <!-- About Section -->
        <section class="page-section" id="about">
          <div class="container position-relative">
            <div class="row mb-xs-40">
              <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">{{ $about?->data['eyebrow'] ?? 'About Us' }}</h2>

                <h3 class="section-title-strong mb-30 mb-xs-20 wow fadeInUp">
                  {{ $about?->title ?? 'Your trusted partner for sparkling clean homes and offices.' }}
                </h3>

                <p class="section-descr mb-40 mb-sm-20 wow fadeInUp" data-wow-delay="0.06s">
                  {{ $about?->content ?? 'At Crystal Service Limited, we believe that a clean environment leads to a better quality of life.' }}
                </p>

                <div class="local-scroll wow fadeInUp" data-wow-delay="0.12s">
                  <a href="/about/" class="link-hover-anim" data-link-animate="y">Learn more about us <i
                      class="mi-arrow-right size-24"></i></a>
                </div>
              </div>
            </div>

            <!-- Images Composition -->
            <div class="row">
              <div class="col-sm-4 mb-xs-50">
                <div class="me-xl-4 pe-sm-2">
                  <div class="composition-1">
                    <div class="composition-1-image-1">
                      <img src="/themes/crystal/images/services/airbnb-cleaning.webp" alt="Airbnb cleaning between guest stays" loading="lazy" />
                    </div>
                    <div class="composition-1-image-2">
                      <img src="/themes/crystal/images/services/about-bg-6.jpg" alt="Professional cleaner at work" loading="lazy" />
                    </div>
                    <div class="composition-1-decoration-1" data-rellax-y data-rellax-speed="-0.5"
                      data-rellax-percentage="0.65">
                      <img src="/themes/crystal/images/demo-fancy/decoration-1.svg" alt="" loading="lazy" />
                    </div>
                    <div class="composition-1-decoration-2" data-rellax-y data-rellax-speed="0.5"
                      data-rellax-percentage="0.2">
                      <img src="/themes/crystal/images/demo-fancy/decoration-2.svg" alt="" loading="lazy" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-4 mb-xs-50">
                <div class="ms-xl-5 ps-sm-2 me-xl-4 pe-sm-2 pe-xl-3">
                  <div class="composition-2">
                    <div class="composition-2-image-1 mt-xs-0">
                      <img src="/themes/crystal/images/services/features-bg.jpg" alt="Cleaner wiping down a surface" loading="lazy" />
                    </div>
                    <div class="composition-2-image-2">
                      <img src="/themes/crystal/images/services/cleaning-supplies.webp" alt="Professional cleaning supplies and equipment" loading="lazy" />
                    </div>
                    <div class="composition-2-decoration" data-rellax-y data-rellax-speed="0.5"
                      data-rellax-percentage="0.2">
                      <img src="/themes/crystal/images/demo-fancy/decoration-3.svg" alt="" loading="lazy" />
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-sm-4">
                <div class="ms-xl-4 ps-sm-2">
                  <div class="composition-3">
                    <div class="composition-3-image-1">
                      <img src="/themes/crystal/images/services/post-3.jpg" alt="Deep cleaning in progress" loading="lazy" />
                    </div>
                    <div class="composition-3-image-2">
                      <img src="/themes/crystal/images/services/post-5.jpg" alt="Office cleaning service" loading="lazy" />
                    </div>
                    <div class="composition-3-decoration-1" data-rellax-y data-rellax-speed="0.5"
                      data-rellax-percentage="0.7">
                      <img src="/themes/crystal/images/demo-fancy/decoration-4.svg" alt="" loading="lazy" />
                    </div>
                    <div class="composition-3-decoration-2" data-rellax-y data-rellax-speed="-0.5"
                      data-rellax-percentage="0.5">
                      <img src="/themes/crystal/images/demo-fancy/decoration-5.svg" alt="" loading="lazy" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Images Composition -->
          </div>
        </section>
        <!-- End About Section -->

        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Cleaning Services Section -->
        <section class="page-section bg-gradient-gray-light-1 bg-scroll light-content" id="services">
          <div class="container position-relative">
            <div class="row mb-60 mb-sm-40">
              <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  {{ $services?->data['eyebrow'] ?? 'Our Services' }}
                </h2>
                <h3 class="section-title mb-0 mb-sm-20 wow fadeInUp">
                  {{ $services?->title ?? 'We provide a wide range of professional cleaning services tailored to meet your needs' }}
                </h3>
              </div>
            </div>

            <div class="row mb-60 mb-sm-40">
              @foreach (array_slice($services?->data['items'] ?? [], 0, 3) as $index => $item)
              <div class="col-md-4 mb-sm-30 text-center wow fadeInUp">
                <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? '' }}" class="w-100 mb-20"
                  style="height: 240px; object-fit: cover; border-radius: 20px;" loading="lazy" />
                <h4 class="services-5-title mb-10">{{ $item['title'] ?? '' }}</h4>
                <p class="services-5-text mb-0">{{ $item['description'] ?? '' }}</p>
              </div>
              @endforeach
            </div>

            <div class="row">
              <div class="col-12 text-center wow fadeInUp">
                <a href="/services/" class="btn btn-mod btn-w btn-large btn-round btn-hover-anim mb-xs-10"><span>View
                    all services</span></a>
              </div>
            </div>
          </div>
        </section>
        <!-- End Cleaning Services Section -->

        <!-- Salon Services Section -->
        <section class="page-section bg-gradient-gray-light-2 bg-scroll" id="salon">
          <div class="container position-relative">
            <div class="row mb-60 mb-sm-40">
              <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  {{ $salon?->data['eyebrow'] ?? 'Salon & Hairdressing' }}
                </h2>
                <h3 class="section-title mb-0 mb-sm-20 wow fadeInUp">
                  {{ $salon?->title ?? 'Now offering professional hairdressing' }}
                </h3>
              </div>
            </div>

            <div class="row mb-60 mb-sm-40">
              @foreach (array_slice($salon?->data['items'] ?? [], 0, 3) as $index => $item)
              <div class="col-md-4 mb-sm-30 text-center wow fadeInUp">
                <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? '' }}" class="w-100 mb-20"
                  style="height: 240px; object-fit: cover; border-radius: 20px;" loading="lazy" />
                <h4 class="services-5-title mb-10">{{ $item['title'] ?? '' }}</h4>
                <p class="services-5-text mb-0">{{ $item['description'] ?? '' }}</p>
              </div>
              @endforeach
            </div>

            <div class="row">
              <div class="col-12 text-center wow fadeInUp">
                <a href="/salon/"
                  class="btn btn-mod btn-color btn-large btn-round btn-hover-anim mb-xs-10"><span>Visit the
                    salon</span></a>
              </div>
            </div>
          </div>
        </section>
        <!-- End Salon Services Section -->

        <!-- Why Choose Us Section -->
        <section class="page-section">
          <div class="container position-relative">
            <div class="row">
              <!-- Image -->
              <div class="col-lg-6 col-xl-6 d-flex align-items-center">
                <div class="w-100">
                  <div class="mb-20">
                    <img src="/themes/crystal/images/services/post-2.jpg" alt="Crystal Services cleaner at work" class="w-100"
                      style="border-radius: 20px" loading="lazy" />
                  </div>
                </div>
              </div>
              <!-- End Images -->

              <!-- Section Text -->
              <div class="col-lg-6 col-xl-6 d-flex align-items-center order-first order-lg-last mb-md-60 mb-sm-40">
                <div class="w-100">
                  <h2 class="section-caption-fancy mb-20 mb-xs-10">
                    Why Choose Us?
                  </h2>

                  <h3 class="section-title-small mb-30">
                    We're dedicated to providing exceptional cleaning services
                    that go beyond the ordinary
                  </h3>

                  <p class="section-descr mb-30">
                    We go beyond just cleaning – we provide peace of mind.
                    Here’s why we stand out as the preferred choice for cleaning
                    services across the UK:
                  </p>

                  <!-- Features List -->
                  <div class="row features-list">
                    <!-- Features List Item -->
                    <div class="col-sm-6 col-lg-12 col-xl-6 d-flex mb-3">
                      <div class="features-list-icon">
                        <i class="mi-check"></i>
                      </div>
                      <div class="features-list-text">
                        Experienced and Professional Team
                      </div>
                    </div>
                    <!-- End Features List Item -->

                    <!-- Features List Item -->
                    <div class="col-sm-6 col-lg-12 col-xl-6 col-lg-6 d-flex mb-3">
                      <div class="features-list-icon">
                        <i class="mi-check"></i>
                      </div>
                      <div class="features-list-text">
                        Tailored Cleaning Solutions
                      </div>
                    </div>
                    <!-- End Features List Item -->

                    <!-- Features List Item -->
                    <div class="col-sm-6 col-lg-12 col-xl-6 d-flex mb-3">
                      <div class="features-list-icon">
                        <i class="mi-check"></i>
                      </div>
                      <div class="features-list-text">
                        Reliable and Flexible Scheduling
                      </div>
                    </div>
                    <!-- End Features List Item -->

                    <!-- Features List Item -->
                    <div class="col-sm-6 col-lg-12 col-xl-6 d-flex mb-3">
                      <div class="features-list-icon">
                        <i class="mi-check"></i>
                      </div>
                      <div class="features-list-text">
                        Customer Satisfaction Guarantee
                      </div>
                    </div>
                    <!-- End Features List Item -->
                  </div>
                  <!-- End Features List -->
                </div>
              </div>
              <!-- End Section Text -->
            </div>
          </div>
        </section>
        <!-- End Why Choose Us Section -->
        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Testimonials Section -->
        <section id="testimonial" class="page-section bg-gradient-gray-light-2 bg-scroll">
          <div class="container position-relative">
            <div class="row">
              <!-- Section Text -->
              <div class="col-lg-5 col-xl-5 d-flex align-items-center mb-md-60 mb-sm-40">
                <div class="w-100">
                  <h2 class="section-caption-fancy mb-20 mb-xs-10">
                    Testimonials
                  </h2>

                  <h3 class="section-title-small mb-30">
                    Trusted by Homes and Businesses Across the UK
                  </h3>

                  <p class="section-descr mb-30">
                    Hear directly from the individuals and businesses who have
                    experienced the Crystal Service difference—exceptional
                    cleaning results, professional teams, and reliable service.
                  </p>

                  <!-- Numbers -->
                  <div class="row">
                    <div class="col-md-6 number-1-item mb-sm-10">
                      <div class="number-1-title">85+</div>
                      <div class="number-1-descr">
                        Homes and offices cleaned
                      </div>
                    </div>

                    <div class="col-md-6 number-1-item">
                      <div class="number-1-title">32+</div>
                      <div class="number-1-descr">Satisfied customers</div>
                    </div>
                  </div>
                  <!-- End Numbers -->
                </div>
              </div>
              <!-- End Section Text -->

              <!-- Testimonials Grid -->
              <div class="col-lg-7 col-xl-6 offset-xl-1 d-flex align-items-center">
                <div class="w-100 position-relative">
                  <!-- Decoration Dots -->
                  <div class="decoration-7 d-none d-sm-block" data-rellax-y data-rellax-speed="0.5"
                    data-rellax-percentage="0.5">
                    <img src="/themes/crystal/images/demo-fancy/decoration-7.svg" alt="" style="color: aquamarine" loading="lazy" />
                  </div>
                  <!-- End Decoration Dots -->

                  <div class="row masonry mb-n30">
                    <!-- Testimonials Item -->
                    <div class="col-md-6 mt-50 mt-sm-0 mb-30">
                      <div class="testimonials-4-item">
                        <div class="testimonials-4-icon">
                          <i class="icon-quotation-mark"></i>
                        </div>
                        <blockquote class="testimonials-4-text">
                          <p class="mb-0">
                            We’ve been using Crystal Service Limited for our
                            office cleaning, and their attention to detail is
                            outstanding. The team is always punctual, and the
                            place looks spotless after every visit. Highly
                            recommend!
                          </p>
                          <footer class="testimonials-4-author mt-30 clearfix">
                            <div class="overflow-hidden">
                              John M.
                              <div class="small">Commercial Client</div>
                            </div>
                          </footer>
                        </blockquote>
                      </div>
                    </div>
                    <!-- End Testimonials Item -->

                    <!-- Testimonials Item -->
                    <div class="col-md-6 mb-30">
                      <div class="testimonials-4-item">
                        <div class="testimonials-4-icon">
                          <i class="icon-quotation-mark"></i>
                        </div>
                        <blockquote class="testimonials-4-text">
                          <p class="mb-0">
                            I needed an end-of-tenancy clean, and Crystal
                            Services came to the rescue. They were professional,
                            worked quickly, and left the place looking brand
                            new. Got my deposit back without a hitch!
                          </p>
                          <footer class="testimonials-4-author mt-30 clearfix">
                            <div class="overflow-hidden">
                              Sarah P
                              <div class="small">Tenant</div>
                            </div>
                          </footer>
                        </blockquote>
                      </div>
                    </div>
                    <!-- End Testimonials Item -->

                    <!-- Testimonials Item -->
                    <div class="col-md-6 mb-30">
                      <div class="testimonials-4-item">
                        <div class="testimonials-4-icon">
                          <i class="icon-quotation-mark"></i>
                        </div>
                        <blockquote class="testimonials-4-text">
                          <p class="mb-0">
                            The deep cleaning service was a game-changer for our
                            home. The team was friendly, efficient, and left
                            every corner of our house looking spotless. We now
                            use them regularly for our domestic cleaning.
                          </p>
                          <footer class="testimonials-4-author mt-30 clearfix">
                            <div class="overflow-hidden">
                              Emily R.
                              <div class="small">Homeowner</div>
                            </div>
                          </footer>
                        </blockquote>
                      </div>
                    </div>
                    <!-- End Testimonials Item -->

                    <!-- Testimonials Item -->
                    <div class="col-md-6 mb-30">
                      <div class="testimonials-4-item">
                        <div class="testimonials-4-icon">
                          <i class="icon-quotation-mark"></i>
                        </div>
                        <blockquote class="testimonials-4-text">
                          <p class="mb-0">
                            As an Airbnb host, cleanliness is a top priority for
                            me. Crystal Service Limited provides timely and
                            thorough cleans between guests, which has improved
                            my guest reviews significantly. Highly reliable!
                          </p>
                          <footer class="testimonials-4-author mt-30 clearfix">
                            <div class="overflow-hidden">
                              David A.
                              <div class="small">Airbnb Host</div>
                            </div>
                          </footer>
                        </blockquote>
                      </div>
                    </div>
                    <!-- End Testimonials Item -->
                  </div>
                </div>
              </div>
              <!-- Testimonials Grid -->
            </div>
          </div>
        </section>
        <!-- End Testimonials Section -->

        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <section class="page-section">
          <div class="container position-relative">
            <!-- Decorative Dots -->
            <div class="decoration-4 d-none d-md-block js-in-viewport" data-rellax-y="" data-rellax-speed="-0.5"
              data-rellax-percentage=".7" style="transform: translate3d(0px, -25px, 0px)">
              <img src="/themes/crystal/images/demo-fancy/decoration-2.svg" alt="" loading="lazy" />
            </div>
            <!-- End Decorative Dots -->

            <div class="row">
              <!-- Text -->
              <div class="col-lg-6 mb-md-60 mb-xs-30 d-flex align-items-center">
                <div class="wow fadeInUp animated" data-wow-duration="1.2s" data-wow-offset="205" style="
                    visibility: visible;
                    animation-duration: 1.2s;
                    animation-name: fadeInUp;
                  ">
                  <div class="row">
                    <div class="col-lg-10">
                      <h2 class="section-title mb-60 mb-sm-30">
                        Clean with us in 4 easy steps
                      </h2>
                    </div>
                  </div>

                  <!-- Features Grid -->
                  <div class="row alt-features-grid">
                    <!-- Features Item -->
                    <div class="col-lg-6">
                      <div class="alt-features-item">
                        <div class="alt-features-icon">
                          <span class="text-gray text">1</span>
                        </div>
                        <h3 class="alt-features-title">Consultation & Quote</h3>
                        <div class="alt-features-descr">
                          Contact us to discuss your cleaning needs. We’ll
                          assess the job and provide a free, no-obligation
                          quote.
                        </div>
                      </div>
                    </div>
                    <!-- End Features Item -->

                    <!-- Features Item -->
                    <div class="col-lg-6">
                      <div class="alt-features-item">
                        <div class="alt-features-icon">
                          <span class="text-gray text">2</span>
                        </div>
                        <h3 class="alt-features-title">
                          Tailored Cleaning Plan
                        </h3>
                        <div class="alt-features-descr">
                          Once we've assessed your cleaning needs, we’ll create
                          a customized cleaning plan that fits your specific
                          requirements. Whether it's a one-time deep clean or
                          regular maintenance.
                        </div>
                      </div>
                    </div>
                    <!-- End Features Item -->

                    <!-- Features Item -->
                    <div class="col-lg-6">
                      <div class="alt-features-item">
                        <div class="alt-features-icon">
                          <span class="text-gray text">3</span>
                        </div>
                        <h3 class="alt-features-title">
                          Professional Cleaning Service
                        </h3>
                        <div class="alt-features-descr">
                          Our trained, insured cleaners arrive with all the
                          necessary supplies and carry out the cleaning tasks to
                          the highest standards.
                        </div>
                      </div>
                    </div>
                    <!-- End Features Item -->

                    <!-- Features Item -->
                    <div class="col-lg-6">
                      <div class="alt-features-item">
                        <div class="alt-features-icon">
                          <span class="text-gray text">4</span>
                        </div>
                        <h3 class="alt-features-title">
                          Final Inspection & Satisfaction
                        </h3>
                        <div class="alt-features-descr">
                          After cleaning, we’ll ensure everything is spotless,
                          allowing you to review and confirm your satisfaction.
                        </div>
                      </div>
                    </div>
                    <!-- End Features Item -->
                  </div>
                  <!-- End Features Grid -->
                </div>
              </div>
              <!-- End Text -->

              <!-- Images -->
              <div class="col-lg-6 d-flex align-items-center">
                <div class="call-action-3-images mt-xs-0 text-end">
                  <div class="call-action-3-image-1">
                    <img src="/themes/crystal/images/services/about-4.webp" alt="Crystal Services team cleaning a home" class="wow scaleOutIn animated"
                      data-wow-duration="1.2s" data-wow-offset="205" style="
                        visibility: visible;
                        animation-duration: 1.2s;
                        animation-name: scaleOutIn;
                        border-radius: 20px;
                      " loading="lazy" />
                  </div>

                  <div class="call-action-3-image-2-wrap d-flex align-items-center">
                    <div class="call-action-3-image-2 js-in-viewport" data-rellax-y="" data-rellax-speed="0.85"
                      data-rellax-percentage="0.5" style="transform: translate3d(0px, 8px, 0px)">
                      <img src="/themes/crystal/images/services/glass-cleaning-lg.webp" alt="Window and glass cleaning"
                        class="wow scaleOutIn animated" data-wow-duration="1.2s" style="
                          visibility: visible;
                          animation-duration: 1.2s;
                          animation-name: scaleOutIn;
                          border-radius: 20px;
                        " loading="lazy" />
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Images -->
            </div>
          </div>
        </section>

        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Pricing Section -->
        <section class="page-section bg-gradient-gray-light-1 bg-scroll light-content" id="pricing">
          <div class="container">
            <div class="row mb-50 mb-sm-30">
              <div class="col-md-8 offset-md-2 text-center">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  Let Us Handle the Dirty Work
                </h2>
                <h3 class="section-title mb-0">
                  Book us today for a professional cleaning and enjoy a spotless
                  space. Fast, and reliable service tailored to your needs.
                </h3>
                <div class="mt-10">
                  <a href="/contact/"
                    class="btn btn-mod btn-color btn-large btn-round btn-hover-anim me-1 mt-40 mb-xs-10">
                    <span>Book a cleaning session</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- End Pricing Section -->

        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Contact Section -->
        <section class="page-section" id="contact">
          <div class="container position-relative">
            <div class="row">
              <!-- Left Column -->
              <div class="col-lg-4 mb-md-50 mb-sm-30 position-relative z-index-1">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">Contact Us</h2>

                <h3 class="section-title mb-50 mb-sm-30">
                  We’re open to talk to good people.
                </h3>

                <!-- Contact Information -->
                <div class="row">
                  <div class="col-md-11">
                    <!-- Address -->
                    <div class="contact-item mb-30 mb-sm-20">
                      <div class="ci-icon">
                        <i class="mi-location"></i>
                      </div>
                      <h4 class="ci-title visually-hidden">Our Address</h4>
                      <div class="ci-text">
                        56 Northfield Street, Worcester, Worcestershire, WR1 1NT
                      </div>
                      <div>
                        <a href="https://www.google.com/maps?q=56+Northfield+Street,+Worcester,+Worcestershire+WR1+1NT"
                          class="link-hover-anim" data-link-animate="y" rel="nofollow noopener" target="_blank">See Map
                          <i class="mi-arrow-right size-18"></i></a>
                      </div>
                    </div>
                    <!-- End Address -->

                    <!-- Email -->
                    <div class="contact-item mb-30 mb-sm-20">
                      <div class="ci-icon">
                        <i class="mi-email"></i>
                      </div>
                      <h4 class="ci-title visually-hidden">Our Email</h4>
                      <div class="ci-text">info@crystalservicesltd.co.uk</div>
                      <div class="ci-text">
                        support@crystalservicesltd.co.uk
                      </div>
                      <div>
                        <a href="mailto:info@crystalservicesltd.co.uk" class="link-hover-anim" data-link-animate="y">Say
                          Hello <i class="mi-arrow-right size-18"></i></a>
                      </div>
                    </div>
                    <!-- End Email -->

                    <!-- Phone -->
                    <div class="contact-item">
                      <div class="ci-icon">
                        <i class="mi-mobile"></i>
                      </div>
                      <h4 class="ci-title visually-hidden">Call Us</h4>
                      <div class="ci-text">
                        +44 771 729 9921
                        <div class="small">(Monday-Friday: 9am to 7pm)</div>
                      </div>
                      <div>
                        <a href="tel:+447717299921" class="link-hover-anim" data-link-animate="y">Call now <i
                            class="mi-arrow-right size-18"></i></a>
                      </div>
                    </div>
                    <!-- End Phone -->
                  </div>
                </div>
                <!-- End Contact Information -->
              </div>
              <!-- End Left Column -->

              <!-- Right Column -->
              <div class="col-lg-8 col-xl-7 offset-xl-1">
                <div class="position-relative">
                  <!-- Decorative Image -->
                  <div class="decoration-11 d-none d-xl-block">
                    <div class="wow fadeInUp">
                      <img src="/themes/crystal/images/demo-fancy/contact-section-image.png" width="225" height="250" alt="" loading="lazy" />
                    </div>
                  </div>
                  <!-- End Decorative Image -->

                  <div class="box-shadow round p-4 p-sm-5 bg-gradient-gray-light-1 bg-scroll">
                    <h4 class="h3 mb-30 form-title">Get in touch <svg width="24" height="24" viewBox="0 0 24 24"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                          d="M5 4H9L11 9L8.5 10.5C9.57096 12.6715 11.3285 14.429 13.5 15.5L15 13L20 15V19C20 19.5304 19.7893 20.0391 19.4142 20.4142C19.0391 20.7893 18.5304 21 18 21C14.0993 20.763 10.4202 19.1065 7.65683 16.3432C4.8935 13.5798 3.23705 9.90074 3 6C3 5.46957 3.21071 4.96086 3.58579 4.58579C3.96086 4.21071 4.46957 4 5 4Z"
                          stroke="#4567ED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                      </svg></h4>

                    <!-- Contact Form -->
                    <form class="form contact-form" id="contact_form" onsubmit="handleFormSubmit(event)">
                      <div class="row">
                        <input type="hidden" name="access_key" value="65e32145-fc60-4ad1-86a7-6398381cfc30" />
                        <input type="hidden" name="subject" value="-- New Service Request Form --" />
                        <div class="mb-3">
                          <div id="success-message" class="d-none"></div>
                          <div id="error-message" class="d-none"></div>
                        </div>
                        <div class="col-md-6">
                          <!-- Name -->
                          <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" class="input-lg round form-control"
                              placeholder="Enter your name" pattern=".{3,100}" required aria-required="true" />
                          </div>
                          <!-- End Name -->
                        </div>

                        <div class="col-md-6">
                          <!-- Email -->
                          <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="input-lg round form-control"
                              placeholder="Enter your email" pattern=".{5,100}" required aria-required="true" />
                          </div>
                          <!-- End Email -->
                        </div>
                      </div>

                      <!-- phone -->
                      <div class="form-group">
                        <label for="Phone Number">Phone Number</label>
                        <input type="text" name="Phone Number" id="Phone Number" class="input-md round form-control"
                          placeholder="+44 9028 XXXXX" required aria-required="true" />
                      </div>

                      <!-- services -->
                      <div class="form-group">
                        <label for="service">Select a service</label>
                        <select name="Service" id="Service" class="input-md round form-control" required="">
                          <option value="General Inquiry">General Inquiry</option>
                          <optgroup label="Cleaning">
                            <option value="Custom Cleaning Inquiry">
                              Custom Cleaning Inquiry
                            </option>
                            <option value="Regular Cleaning">
                              Regular Cleaning
                            </option>
                            <option value="Commercial Cleaning">
                              Commercial Cleaning
                            </option>
                            <option value="Deep Cleaning">Deep Cleaning</option>
                            <option value="Office Cleaning">Office Cleaning</option>
                            <option value="Carpet &amp; Upholstery Cleaning">
                              Carpet &amp; Upholstery Cleaning
                            </option>
                            <option value="Oven &amp; Appliances Cleaning">
                              Oven &amp; Appliances Cleaning
                            </option>
                            <option value="End-of-Tenancy Cleaning">
                              End-of-Tenancy Cleaning
                            </option>
                            <option value="Airbnb Cleaning">Airbnb Cleaning</option>
                            <option value="After Builders Cleaning">
                              After Builders Cleaning
                            </option>
                          </optgroup>
                          <optgroup label="Salon &amp; Hairdressing">
                            <option value="Braiding">Braiding</option>
                            <option value="Sew-in">Sew-in</option>
                            <option value="Dreadlocks">Dreadlocks</option>
                            <option value="Re-locking">Re-locking</option>
                            <option value="Relaxing">Relaxing</option>
                            <option value="Washing &amp; setting">
                              Washing &amp; setting
                            </option>
                            <option value="Natural weaving">Natural weaving</option>
                            <option value="Hair products &amp; attachments">
                              Hair products &amp; attachments
                            </option>
                          </optgroup>
                        </select>
                      </div>

                      <!-- Message -->
                      <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="input-lg round form-control" style="height: 130px"
                          placeholder="Enter your message"></textarea>
                      </div>

                      <div class="row">
                        <div class="col-md-6 col-xl-5">
                          <!-- Send Button -->
                          <div class="pt-3">
                            <button class="submit_btn btn btn-mod btn-gray btn-large btn-round btn-hover-anim"
                              id="submit_btn" type="submit">
                              <span>Send Message</span>
                            </button>
                          </div>
                          <!-- End Send Button -->
                        </div>
                      </div>

                      <div id="result" role="region" aria-live="polite" aria-atomic="true"></div>
                    </form>
                    <!-- End Contact Form -->
                  </div>
                </div>
              </div>
              <!-- End Right Column -->
            </div>
          </div>
        </section>
        <!-- End Contact Section -->
      </main>

      <!-- Footer -->
      <footer class="page-section footer bg-dark-1 light-content pb-30">
        <div class="container">
          <div class="row pb-120 pb-sm-80 pb-xs-50">
            <div class="col-lg-4 text-gray mb-md-50">
              <div class="mb-30">
                <img src="/themes/crystal/images/logo/logo-light.svg" alt="Crystal Services Limited" width="166" height="50" loading="lazy" />
              </div>

              <p>
                Experience the Crystal Service touch – from the first sparkle to
                the lasting freshness of a perfectly cleaned, welcoming space.
              </p>

              <div class="clearlinks">
                <strong>Tel:</strong>
                <a href="tel:+447717299921">+44 771 729 9921</a>
              </div>

              <div class="clearlinks">
                <strong>Email:</strong>
                <a href="mailto: info@crystalservicesltd.co.uk">
                  info@crystalservicesltd.co.uk,</a>
                <a href="mailto: support@crystalservicesltd.co.uk">
                  support@crystalservicesltd.co.uk</a>
              </div>
            </div>

            <div class="col-lg-6 offset-lg-2">
              <div class="row mt-n30">
                <!-- Footer Widget -->
                <div class="col-sm-6 mt-30">
                  <h3 class="fw-title">Company</h3>

                  <ul class="fw-menu clearlist local-scroll">
                    <li><a href="/about/">About us</a></li>
                    <li><a href="/services/">Commercial Cleaning</a></li>
                    <li><a href="/services/"> Upholstery Cleaning</a></li>
                    <li><a href="/services/">Airbnb Cleaning</a></li>
                    <li><a href="/services/">Deep Cleaning</a></li>
                    <li><a href="/contact/">Book a Cleaning</a></li>
                    <li><a href="/careers/">Careers</a></li>
                  </ul>
                </div>
                <!-- End Footer Widget -->

                <!-- Footer Widget -->
                <div class="col-sm-6 mt-30">
                  <h3 class="fw-title">Social Media</h3>

                  <ul class="fw-menu clearlist">
                    <li>
                      <a href="#" rel="noopener nofollow" target="_blank">
                        <i class="fa-facebook"></i>
                        Facebook
                      </a>
                    </li>
                    <li>
                      <a href="#" rel="noopener nofollow" target="_blank">
                        <i class="fa-instagram"></i>
                        Instagram
                      </a>
                    </li>
                    <li>
                      <a href="#" rel="noopener nofollow" target="_blank">
                        <i class="fa-tiktok"></i>
                        Tiktok
                      </a>
                    </li>
                  </ul>
                </div>
                <!-- End Footer Widget -->
              </div>
            </div>
          </div>

          <!-- Footer Text -->
          <div class="row text-gray">
            <div class="col-md-4 col-lg-3">
              <b>© Crystal Services LTD
                <span class="date">
                  <script>
                    document.write(new Date().getFullYear());
                  </script>
                </span></b>
            </div>

            <div class="col-md-7 offset-md-1 offset-lg-2 clearfix">
              <b>56 Northfield Street, Worcester, Worcestershire, WR1 1NT.</b>

              <!-- Back to Top Link -->
              <div class="local-scroll float-end mt-n20 mt-sm-10">
                <a href="#top" class="link-to-top">
                  <i class="mi-arrow-up size-24"></i>
                  <span class="visually-hidden">Scroll to top</span>
                </a>
              </div>
              <!-- End Back to Top Link -->
            </div>
          </div>
          <!-- End Footer Text -->
        </div>
      </footer>
      <!-- End Footer -->
    </div>
    <!-- End Page Wrap -->

    <!-- JS -->
    <script src="/themes/crystal/js/jquery.min.js"></script>
    <script src="/themes/crystal/js/bootstrap.bundle.min.js"></script>
    <script src="/themes/crystal/js/plugins.js"></script>
    <script src="/themes/crystal/js/jquery.ajaxchimp.min.js"></script>
    <script src="/themes/crystal/js/contact-form.js"></script>
    <script src="/themes/crystal/js/all.js"></script>
    <!-- End JS -->
  </body>

</html>

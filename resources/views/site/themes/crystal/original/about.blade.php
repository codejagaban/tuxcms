@php($hero = $cmsSections->get('hero'))
@php($story = $cmsSections->get('story'))
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>About Us &mdash; Crystal Services Limited, Worcester</title>
    <meta
      name="description"
      content="Crystal Services Limited is a family-run Worcester business offering professional cleaning and salon hairdressing. Learn about our mission, our team and our two services."
    />
    <meta name="keywords" content="about Crystal Services Limited, cleaning company Worcester, family-run salon Worcester, professional cleaners" />
    <link rel="canonical" href="{{ rtrim(\App\Support\Site::url(), '/') }}/about/" />
    <meta name="robots" content="index, follow" />
    <!-- Open Graph Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Crystal Services Limited" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:title" content="About Us &mdash; Crystal Services Limited, Worcester" />
    <meta property="og:description" content="Crystal Services Limited is a family-run Worcester business offering professional cleaning and salon hairdressing. Learn about our mission, our team and our two services." />
    <meta property="og:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />
    <meta property="og:url" content="{{ rtrim(\App\Support\Site::url(), '/') }}/about/" />
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="About Us &mdash; Crystal Services Limited, Worcester" />
    <meta name="twitter:description" content="Crystal Services Limited is a family-run Worcester business offering professional cleaning and salon hairdressing. Learn about our mission, our team and our two services." />
    <meta name="twitter:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />

    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link
      rel="icon"
      href="/themes/crystal/images/logo/favicon.png"
      type="image/png"
      sizes="any"
    />
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
    <link rel="stylesheet" href="/themes/crystal/css/demo-fancy/demo-fancy.css?v=3" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
      rel="stylesheet"
    />
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
      "telephone": "+447717299921",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "56 Northfield Street",
        "addressLocality": "Worcester",
        "addressRegion": "Worcestershire",
        "postalCode": "WR1 1NT",
        "addressCountry": "GB"
      }
    },
    {
      "@type": "AboutPage",
      "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/about/",
      "url": "{{ rtrim(\App\Support\Site::url(), '/') }}/about/",
      "name": "About Crystal Services Limited",
      "about": {
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
                            <img src="/themes/crystal/images/logo/logo.svg" alt="Crystal Services Limited" width="159"
                            height="48" />
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
                            <li><a href="/">Home</a></li>
                            <li><a href="/about/" class="active">About</a></li>
                            <li><a href="/services/">Services</a></li>
                            <li><a href="/salon/">Salon</a></li>
                            <li><a href="/contact/">Contact</a></li>
                            <li><a href="/careers/">Careers</a></li>

                            <li class="desktop-nav-display">
                                <div class="vr mt-2"></div>
                            </li>


                            <li>
                                <a href="/contact/" class="opacity-1 no-hover">
                                    <span class="btn btn-mod btn-w btn-border-c btn-small btn-round"
                                        data-btn-animate="y">Get in touch <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M5 4H9L11 9L8.5 10.5C9.57096 12.6715 11.3285 14.429 13.5 15.5L15 13L20 15V19C20 19.5304 19.7893 20.0391 19.4142 20.4142C19.0391 20.7893 18.5304 21 18 21C14.0993 20.763 10.4202 19.1065 7.65683 16.3432C4.8935 13.5798 3.23705 9.90074 3 6C3 5.46957 3.21071 4.96086 3.58579 4.58579C3.96086 4.21071 4.46957 4 5 4Z" stroke="#4567ED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg></span>
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
        <section
          class="page-section bg-gradient-gray-light-2 overflow-hidden"
        >
          <!-- Background Shape -->
          <div class="bg-shape-1 wow fadeIn">
            <img src="/themes/crystal/images/demo-fancy/bg-shape-1.svg" alt="" />
          </div>
          <!-- End Background Shape -->

          <div class="container position-relative pt-10 pt-sm-40 text-center">
            <div class="row">
              <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
                <h1 class="hs-title-10 mb-10">
                  <span class="wow charsAnimIn" data-splitting="chars">
                    {{ $hero?->title ?? 'About us' }}
                  </span>
                </h1>

                <p class="section-descr mb-0 wow fadeIn" data-wow-delay="0.2s">
                  {{ $hero?->content ?? "Learn about us, our commitment to quality, and why we're the best choice for your cleaning and hairdressing needs" }}
                </p>
              </div>
            </div>
          </div>
        </section>
        <!-- End Home Section -->

        <!-- About Section -->
        <section class="page-section" id="about">
          <div class="container position-relative">
            <div class="row mb-xs-40">
              <div
                class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center"
              >
                <p
                  class="section-descr mb-40 mb-sm-20 wow fadeInUp"
                  data-wow-offset="0"
                >
                  {!! nl2br(e($story?->content ?? 'At Crystal Service Limited, we believe that a clean environment leads to a better quality of life.')) !!}
                </p>

                <div class="local-scroll wow fadeInUp" data-wow-delay="0.12s">
                  <a
                    href="/services/"
                    class="link-hover-anim"
                    data-link-animate="y"
                    >View our services <i class="mi-arrow-right size-24"></i
                  ></a>
                </div>
              </div>
            </div>

            <!-- Images Composition -->
            <div class="row">
                <div class="col-sm-4 mb-xs-50">
                  <div class="me-xl-4 pe-sm-2">
                    <div class="composition-1">
                      <div class="composition-1-image-1">
                        <img
                          src="/themes/crystal/images/services/airbnb-cleaning.webp"
                          alt="Airbnb cleaning between guest stays" loading="lazy" />
                      </div>
                      <div class="composition-1-image-2">
                        <img
                          src="/themes/crystal/images/services/about-bg-6.jpg"
                          alt="Professional cleaner at work" loading="lazy" />
                      </div>
                      <div
                        class="composition-1-decoration-1"
                        data-rellax-y
                        data-rellax-speed="-0.5"
                        data-rellax-percentage="0.65"
                      >
                        <img src="/themes/crystal/images/demo-fancy/decoration-1.svg" alt="" loading="lazy" />
                      </div>
                      <div
                        class="composition-1-decoration-2"
                        data-rellax-y
                        data-rellax-speed="0.5"
                        data-rellax-percentage="0.2"
                      >
                        <img src="/themes/crystal/images/demo-fancy/decoration-2.svg" alt="" loading="lazy" />
                      </div>
                    </div>
                  </div>
                </div>
  
                <div class="col-sm-4 mb-xs-50">
                  <div class="ms-xl-5 ps-sm-2 me-xl-4 pe-sm-2 pe-xl-3">
                    <div class="composition-2">
                      <div class="composition-2-image-1 mt-xs-0">
                        <img
                          src="/themes/crystal/images/services/features-bg.jpg"
                          alt="Cleaner wiping down a surface" loading="lazy" />
                      </div>
                      <div class="composition-2-image-2">
                        <img
                          src="/themes/crystal/images/services/cleaning-supplies.webp"
                          alt="Professional cleaning supplies and equipment" loading="lazy" />
                      </div>
                      <div
                        class="composition-2-decoration"
                        data-rellax-y
                        data-rellax-speed="0.5"
                        data-rellax-percentage="0.2"
                      >
                        <img src="/themes/crystal/images/demo-fancy/decoration-3.svg" alt="" loading="lazy" />
                      </div>
                    </div>
                  </div>
                </div>
  
                <div class="col-sm-4">
                  <div class="ms-xl-4 ps-sm-2">
                    <div class="composition-3">
                      <div class="composition-3-image-1">
                        <img
                          src="/themes/crystal/images/services/post-3.jpg"
                          alt="Deep cleaning in progress" loading="lazy" />
                      </div>
                      <div class="composition-3-image-2">
                        <img
                          src="/themes/crystal/images/services/post-5.jpg"
                          alt="Office cleaning service" loading="lazy" />
                      </div>
                      <div
                        class="composition-3-decoration-1"
                        data-rellax-y
                        data-rellax-speed="0.5"
                        data-rellax-percentage="0.7"
                      >
                        <img src="/themes/crystal/images/demo-fancy/decoration-4.svg" alt="" loading="lazy" />
                      </div>
                      <div
                        class="composition-3-decoration-2"
                        data-rellax-y
                        data-rellax-speed="-0.5"
                        data-rellax-percentage="0.5"
                      >
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

        <!-- About Section -->
        <section class="page-section bg-gradient-gray-light-1 bg-scroll light-content" id="about">
          <div class="container position-relative">
            <div class="row mb-60 mb-xs-30">
              <div class="col-md-6">
                <h2 class="section-caption-fancy mb-xs-10">
                  Our Cleaning Business
                </h2>
                <h3 class="section-title mb-0">
                  <span class="wow charsAnimIn" data-splitting="chars"
                    >Crystal Clean: Your Trusted Cleaning Experts</span
                  >
                </h3>
              </div>
            </div>

            <div class="row wow fadeInUp" data-wow-delay="0.5s">
              <div class="col-lg-6 mb-md-60">
                <div class="position-relative">
                  <!-- Image -->
                  <div class="position-relative overflow-hidden">
                    <img
                      src="/themes/crystal/images/services/furnitures-cleaning.webp"
                      class="image-fullwidth relative"
                      alt="Upholstery and furniture cleaning"
                      style="border-radius: 20px;" loading="lazy" />
                  </div>
                  <!-- End Image -->
                </div>
              </div>

              <div class="col-lg-6 col-xl-5 offset-xl-1">
                <h4 class="h5">Our Mission</h4>

                <p class="text-gray">
                  Our mission is simple: to provide reliable, efficient, and
                  top-tier cleaning services tailored to each client’s specific
                  needs. We take pride in our attention to detail and
                  customer-first approach, ensuring that every cleaning project
                  exceeds expectations.
                </p>

                <h4 class="h5">Our Vision</h4>

                <p class="text-gray">
                  From small residential properties to large commercial spaces,
                  we bring professionalism, trust, and a fresh approach to every
                  cleaning job. With years of experience, cutting-edge cleaning
                  methods, and a passionate team, Crystal Service Limited is
                  your trusted partner for all things clean.
                </p>
              </div>
            </div>
          </div>
        </section>
        <!-- End About Section -->


         <!-- Divider -->
         <hr class="mt-0 mb-0" />
         <!-- End Divider -->

        <!-- Salon Section -->
        <section class="page-section" id="salon">
          <div class="container position-relative">
            <div class="row mb-60 mb-xs-30">
              <div class="col-md-8">
                <h2 class="section-caption-fancy mb-xs-10">
                  Our Salon Business
                </h2>
                <h3 class="section-title mb-0">
                  <span class="wow charsAnimIn" data-splitting="chars"
                    >Crystal Salon: Beautiful Hair, Same Trusted Care</span
                  >
                </h3>
              </div>
            </div>

            <div class="row wow fadeInUp" data-wow-delay="0.5s">
              <div class="col-lg-6 col-xl-5 mb-md-60">
                <h4 class="h5">Hair, styled with care</h4>

                <p class="text-gray">
                  Crystal isn't only about spotless spaces. Our family-run
                  salon brings the same care and attention to your hair —
                  offering braiding, sew-ins, dreadlocks, re-locking, relaxing,
                  washing &amp; setting and natural weaving, all tailored to
                  your hair type and the look you want.
                </p>

                <h4 class="h5">Products &amp; attachments</h4>

                <p class="text-gray">
                  We also sell a wide range of hair treatments, products and
                  attachments. Follow us on TikTok to see what's available, or
                  book your next style at our Worcester salon.
                </p>

                <div class="mt-30 local-scroll">
                  <a
                    href="/salon/"
                    class="btn btn-mod btn-color btn-large btn-round btn-hover-anim"
                    ><span>Visit the salon</span></a
                  >
                </div>
              </div>

              <div class="col-lg-6 offset-xl-1">
                <div class="position-relative overflow-hidden">
                  <img
                    src="/themes/crystal/images/salon/salon-about.jpg"
                    class="image-fullwidth relative"
                    alt="Crystal Salon &amp; Hairdressing"
                    style="border-radius: 20px;" loading="lazy" />
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- End Salon Section -->

         <!-- Divider -->
         <hr class="mt-0 mb-0" />
         <!-- End Divider -->


        <!-- Why Choose Us Section -->
        <section class="page-section">
          <div class="container position-relative">
            <div class="row">
              <!-- Image -->
              <div class="col-lg-6 col-xl-6 d-flex align-items-center">
                <div class="w-100">
                  <div class="mb-20">
                    <img
                      src="/themes/crystal/images/services/post-2.jpg"
                      alt="Crystal Services cleaner at work"
                      class="w-100"
                      style="border-radius: 20px;" loading="lazy" />
                  </div>
                </div>
              </div>
              <!-- End Images -->

              <!-- Section Text -->
              <div
                class="col-lg-6 col-xl-6 d-flex align-items-center order-first order-lg-last mb-md-60 mb-sm-40"
              >
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
                    <div
                      class="col-sm-6 col-lg-12 col-xl-6 col-lg-6 d-flex mb-3"
                    >
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
              <div
                class="col-lg-5 col-xl-5 d-flex align-items-center mb-md-60 mb-sm-40"
              >
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
              <div
                class="col-lg-7 col-xl-6 offset-xl-1 d-flex align-items-center"
              >
                <div class="w-100 position-relative">
                  <!-- Decoration Dots -->
                  <div
                    class="decoration-7 d-none d-sm-block"
                    data-rellax-y
                    data-rellax-speed="0.5"
                    data-rellax-percentage="0.5"
                  >
                    <img
                      src="/themes/crystal/images/demo-fancy/decoration-7.svg"
                      alt=""
                      style="color: aquamarine" loading="lazy" />
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
            <div
              class="decoration-4 d-none d-md-block js-in-viewport"
              data-rellax-y=""
              data-rellax-speed="-0.5"
              data-rellax-percentage=".7"
              style="transform: translate3d(0px, -25px, 0px)"
            >
              <img src="/themes/crystal/images/demo-fancy/decoration-2.svg" alt="" loading="lazy" />
            </div>
            <!-- End Decorative Dots -->

            <div class="row">
              <!-- Text -->
              <div class="col-lg-6 mb-md-60 mb-xs-30 d-flex align-items-center">
                <div
                  class="wow fadeInUp animated"
                  data-wow-duration="1.2s"
                  data-wow-offset="205"
                  style="
                    visibility: visible;
                    animation-duration: 1.2s;
                    animation-name: fadeInUp;
                  "
                >
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
                    <img
                      src="/themes/crystal/images/services/about-4.webp"
                      alt="Crystal Services team cleaning a home"
                      class="wow scaleOutIn animated"
                      data-wow-duration="1.2s"
                      data-wow-offset="205"
                      style="
                        visibility: visible;
                        animation-duration: 1.2s;
                        animation-name: scaleOutIn;
                        border-radius: 20px;
                      " loading="lazy" />
                  </div>

                  <div
                    class="call-action-3-image-2-wrap d-flex align-items-center"
                  >
                    <div
                      class="call-action-3-image-2 js-in-viewport"
                      data-rellax-y=""
                      data-rellax-speed="0.85"
                      data-rellax-percentage="0.5"
                      style="transform: translate3d(0px, 8px, 0px)"
                    >
                      <img
                        src="/themes/crystal/images/services/glass-cleaning-lg.webp"
                        alt="Window and glass cleaning"
                        class="wow scaleOutIn animated"
                        data-wow-duration="1.2s"
                        style="
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
                  <a
                    href="/contact/"
                    class="btn btn-mod btn-color btn-large btn-round btn-hover-anim me-1 mt-40 mb-xs-10"
                  >
                    <span>Book a cleaning session</span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- End Pricing Section -->
      </main>
      @include('site.themes.crystal.partials.footer')

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

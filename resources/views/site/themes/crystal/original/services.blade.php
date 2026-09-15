@php($hero = $cmsSections->get('hero'))
@php($services = $cmsSections->get('services'))
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Cleaning Services in Worcester &mdash; Crystal Services Limited</title>
    <meta
      name="description"
      content="Commercial, deep, end-of-tenancy, Airbnb, upholstery and after-builders cleaning in Worcester &mdash; plus professional salon &amp; hairdressing. See all our services and book today."
    />
    <meta name="keywords" content="cleaning services Worcester, commercial cleaning, upholstery cleaning, end of tenancy cleaning, Airbnb cleaning, deep cleaning, after builders cleaning" />
    <link rel="canonical" href="{{ rtrim(\App\Support\Site::url(), '/') }}/services/" />
    <meta name="robots" content="index, follow" />
    <!-- Open Graph Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Crystal Services Limited" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:title" content="Cleaning Services in Worcester &mdash; Crystal Services Limited" />
    <meta property="og:description" content="Commercial, deep, end-of-tenancy, Airbnb, upholstery and after-builders cleaning in Worcester &mdash; plus professional salon &amp; hairdressing. See all our services and book today." />
    <meta property="og:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />
    <meta property="og:url" content="{{ rtrim(\App\Support\Site::url(), '/') }}/services/" />
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Cleaning Services in Worcester &mdash; Crystal Services Limited" />
    <meta name="twitter:description" content="Commercial, deep, end-of-tenancy, Airbnb, upholstery and after-builders cleaning in Worcester &mdash; plus professional salon &amp; hairdressing. See all our services and book today." />
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
      "@type": "ItemList",
      "name": "Cleaning services",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "item": {
            "@type": "Service",
            "name": "Commercial Cleaning",
            "description": "Cleaning for offices, retail spaces and other commercial properties, scheduled around your business.",
            "provider": {
              "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
            },
            "areaServed": {
              "@type": "City",
              "name": "Worcester"
            }
          }
        },
        {
          "@type": "ListItem",
          "position": 2,
          "item": {
            "@type": "Service",
            "name": "Upholstery Cleaning",
            "description": "Stain, dirt and allergen removal for fabric and leather furniture.",
            "provider": {
              "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
            },
            "areaServed": {
              "@type": "City",
              "name": "Worcester"
            }
          }
        },
        {
          "@type": "ListItem",
          "position": 3,
          "item": {
            "@type": "Service",
            "name": "End-of-Tenancy Cleaning",
            "description": "Complete move-out cleaning to help tenants secure their deposit.",
            "provider": {
              "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
            },
            "areaServed": {
              "@type": "City",
              "name": "Worcester"
            }
          }
        },
        {
          "@type": "ListItem",
          "position": 4,
          "item": {
            "@type": "Service",
            "name": "Airbnb Cleaning",
            "description": "Fast, thorough turnaround cleaning between guest stays.",
            "provider": {
              "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
            },
            "areaServed": {
              "@type": "City",
              "name": "Worcester"
            }
          }
        },
        {
          "@type": "ListItem",
          "position": 5,
          "item": {
            "@type": "Service",
            "name": "Deep Cleaning",
            "description": "Intensive top-to-bottom cleaning for homes and offices.",
            "provider": {
              "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
            },
            "areaServed": {
              "@type": "City",
              "name": "Worcester"
            }
          }
        },
        {
          "@type": "ListItem",
          "position": 6,
          "item": {
            "@type": "Service",
            "name": "After Builders Cleaning",
            "description": "Post-construction and renovation cleaning, removing dust and debris.",
            "provider": {
              "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
            },
            "areaServed": {
              "@type": "City",
              "name": "Worcester"
            }
          }
        }
      ]
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "What types of cleaning services do you offer?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "We provide a wide range of cleaning services, including residential cleaning, office cleaning, commercial space cleaning, deep cleaning, and post-construction cleaning. We tailor our services to meet your specific needs, ensuring your space stays spotless and welcoming."
          }
        },
        {
          "@type": "Question",
          "name": "How do you ensure the safety and security of my property during cleaning?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Our team is fully trained, insured, and background-checked for your peace of mind. We take extra precautions to safeguard your property, and our cleaning professionals follow strict protocols to ensure both security and the highest cleaning standards."
          }
        },
        {
          "@type": "Question",
          "name": "Do I need to provide cleaning supplies and equipment?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "No, we bring our own high-quality, eco-friendly cleaning supplies and equipment. However, if you have specific products you'd like us to use, we're happy to accommodate your preferences."
          }
        },
        {
          "@type": "Question",
          "name": "How can I schedule or reschedule a cleaning service?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Scheduling or rescheduling a cleaning is easy. Simply contact us via phone, email, or our website, and we'll work with you to find a convenient time. We offer flexible scheduling options to fit your needs."
          }
        },
        {
          "@type": "Question",
          "name": "What is the cancellation policy for your cleaning services?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "We understand that plans can change. If you need to cancel or reschedule your appointment, please notify us at least 24 hours in advance. Cancellations made within less than 24 hours may be subject to a cancellation fee."
          }
        },
        {
          "@type": "Question",
          "name": "Are your cleaning services customizable?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Absolutely! We offer fully customizable cleaning plans based on your preferences and specific needs. Whether you require a one-time deep clean or ongoing scheduled services, we'll tailor our approach to ensure the best results for your home or business."
          }
        }
      ]
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
      @include('site.themes.crystal.partials.nav')


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
                    {{ $hero?->title ?? 'Our Services' }}
                  </span>
                </h1>

                <p class="section-descr mb-0 wow fadeIn" data-wow-delay="0.2s">
                  {{ $hero?->content ?? 'We provide a wide range of professional cleaning services tailored to your needs.' }}
                </p>
              </div>
            </div>
          </div>
        </section>
        <!-- End Home Section -->

        <!-- Services Section -->
        <section class="page-section" id="services">
          <div class="container position-relative">
            <!-- Services Grid -->
            <div class="row services-5-grid">
              <!-- Services Item -->
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/about/services-6.png"
                        width="198"
                        height="198"
                        alt="Commercial cleaning service"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Commercial Cleaning</h4>
                        <p class="services-5-text mb-0">
                          We understand the importance of a clean and organized
                          workspace for productivity and employee well-being.
                          Our commercial cleaning service caters to offices,
                          retail spaces, and other commercial properties. We
                          work around your schedule to ensure minimal
                          disruption, providing daily, weekly, or one-time deep
                          cleans.
                        </p>
                        <a
                          href="/contact/?service=Commercial%20Cleaning#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                        >
                          <span>Book this service</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Services Item -->

              <!-- Services Item -->
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/about/services-3.png"
                        width="198"
                        height="198"
                        alt="Upholstery cleaning service"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Upholstery Cleaning</h4>
                        <p class="services-5-text mb-0">
                          Our upholstery cleaning service uses advanced
                          techniques to remove stains, dirt, and allergens from
                          your furniture, leaving it looking fresh and extending
                          its lifespan. Whether it’s fabric or leather, our team
                          carefully handles each piece to maintain its quality.
                        </p>
                        <a
                          href="/contact/?service=Carpet%20%26%20Upholstery%20Cleaning#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                        >
                          <span>Book this service</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Services Item -->

              <!-- Services Item -->
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/about/services-0.png"
                        width="198"
                        height="198"
                        alt="End-of-tenancy cleaning service"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">
                          End-of-Tenancy Cleaning
                        </h4>
                        <p class="services-5-text mb-0">
                          Moving out? Our end-of-tenancy cleaning service is
                          designed to help tenants leave their property in
                          pristine condition, ensuring the return of their
                          security deposit. We clean every corner of the
                          property, including carpets, bathrooms, kitchens, and
                          windows, to meet landlord or agent expectations.
                        </p>
                        <a
                          href="/contact/?service=End-of-Tenancy%20Cleaning#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                        >
                          <span>Book this service</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Services Item -->

              <!-- Services Item -->
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/about/services-2.png"
                        width="198"
                        height="198"
                        alt="Airbnb cleaning service"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Airbnb Cleaning</h4>
                        <p class="services-5-text mb-0">
                          Make a lasting impression on your guests with our
                          specialized Airbnb cleaning service. We provide quick
                          and thorough cleans between guest stays, ensuring your
                          property is always guest-ready with fresh linens,
                          clean bathrooms, and a spotless living area.
                        </p>
                        <a
                          href="/contact/?service=Airbnb%20Cleaning#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                        >
                          <span>Book this service</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Services Item -->
              <!-- Services Item -->
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/about/services-5.png"
                        width="198"
                        height="198"
                        alt="Deep cleaning service"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Deep Cleaning</h4>
                        <p class="services-5-text mb-0">
                          Our deep cleaning service is perfect for homes or
                          offices that need more than just a surface clean. We
                          tackle those hard-to-reach areas, scrubbing floors,
                          disinfecting surfaces, and removing built-up grime
                          from kitchens, bathrooms, and other high-traffic
                          spaces.
                        </p>
                        <a
                          href="/contact/?service=Deep%20Cleaning#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                        >
                          <span>Book this service</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Services Item -->
              <!-- Services Item -->
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/services/project-single-1.jpg"
                        width="198"
                        height="198"
                        alt="After builders cleaning service"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">
                          After Builders Cleaning
                        </h4>
                        <p class="services-5-text mb-0">
                          Renovations can leave a mess behind. Our
                          after-builders cleaning service ensures that your
                          property is spotless once construction or renovations
                          are completed. We remove dust, debris, and other
                          post-construction residues, leaving your space ready
                          for use.
                        </p>
                        <a
                          href="/contact/?service=After%20Builders%20Cleaning#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                        >
                          <span>Book this service</span>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Services Item -->
            </div>
            <!-- End Services Grid -->
          </div>
        </section>
        <!-- End Services Section -->
        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Salon & Hairdressing Section -->
        <section class="page-section" id="salon">
          <div class="container position-relative">
            <div class="row mb-60 mb-sm-40">
              <div
                class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center"
              >
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  Salon &amp; Hairdressing
                </h2>
                <h3 class="section-title mb-30 mb-sm-20 wow fadeInUp">
                  Now offering professional hairdressing
                </h3>
                <p
                  class="section-descr mb-0 wow fadeInUp"
                  data-wow-delay="0.06s"
                >
                  Alongside our cleaning services, we now offer professional
                  hairdressing. Book a style or shop hair products and
                  attachments.
                </p>
              </div>
            </div>

            <!-- Salon Services Grid -->
            <div class="row services-5-grid">
              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/braiding.jpg"
                        width="198"
                        height="198"
                        alt="Braiding"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Braiding</h4>
                        <p class="services-5-text mb-0">
                          From box braids and cornrows to knotless and feed-in styles, our braiding is done with care and precision. We keep tension gentle on your scalp so every braid is neat, comfortable and protective — a style that lasts for weeks while keeping your natural hair healthy underneath.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/sew-in.jpg"
                        width="198"
                        height="198"
                        alt="Sew-in"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Sew-in</h4>
                        <p class="services-5-text mb-0">
                          Our sew-in weaves are professionally installed on a secure braided foundation for a flawless, natural-looking finish. Whether you want extra length, fuller volume or a whole new look, we blend the weave seamlessly with your own hair and make sure it sits comfortably from day one.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/dreadlocks.jpg"
                        width="198"
                        height="198"
                        alt="Dreadlocks"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Dreadlocks</h4>
                        <p class="services-5-text mb-0">
                          Starting your loc journey or maintaining mature locs? We offer starter locs, root maintenance and styling, all done with healthy hair practices. We help you choose the right size and pattern for your hair type, and keep your locs clean, strong and well-shaped as they grow.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/re-locking.jpg"
                        width="198"
                        height="198"
                        alt="Re-locking"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Re-locking</h4>
                        <p class="services-5-text mb-0">
                          Regular retwisting keeps your locs looking sharp. We carefully re-lock the new growth at your roots so every loc stays neat and uniform, without over-twisting — protecting your edges and encouraging strong, healthy growth between appointments.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/relaxing.jpg"
                        width="198"
                        height="198"
                        alt="Relaxing"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Relaxing</h4>
                        <p class="services-5-text mb-0">
                          Our relaxer treatments are applied with care to smooth and soften your natural texture while keeping your hair healthy. We assess your hair first, choose the right strength for you, and finish with a deep condition, leaving your hair silky, manageable and full of movement.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/washing-and-setting.jpg"
                        width="198"
                        height="198"
                        alt="Washing & setting"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Washing &amp; setting</h4>
                        <p class="services-5-text mb-0">
                          A thorough cleanse, deep condition and professional set, tailored to your hair. Whether you prefer rollers, a sleek blow-dry or a bouncy curled finish, you leave with fresh, healthy-looking hair that holds its shape for days.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-stretch">
                <div
                  class="services-5-item d-flex align-items-stretch text-center text-xl-start"
                >
                  <div class="d-xl-flex wow fadeInUpShort" data-wow-offset="0">
                    <div class="services-5-image mb-lg-20 me-xl-4">
                      <img
                        src="/themes/crystal/images/salon/natural-weaving.jpg"
                        width="198"
                        height="198"
                        alt="Natural weaving"
                        style="border-radius: 20px; object-fit: cover" loading="lazy" />
                    </div>
                    <div class="services-5-body d-flex align-items-center">
                      <div class="w-100">
                        <h4 class="services-5-title">Natural weaving</h4>
                        <p class="services-5-text mb-0">
                          Our natural weaving blends quality extensions with your own hair for a look that is full, flowing and completely believable. We match texture and colour carefully and install with a technique that protects your natural hair, so you get a beautiful style without the damage.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Salon Services Grid -->

            <div class="row mt-40 mt-sm-20">
              <div class="col-12 text-center local-scroll">
                <a
                  href="/salon/"
                  class="btn btn-mod btn-color btn-large btn-round btn-hover-anim me-1 mb-xs-10"
                  ><span>Explore the salon</span></a
                >
                <a
                  href="#"
                  class="btn btn-mod btn-border-c btn-large btn-round mb-xs-10"
                  target="_blank"
                  rel="noopener noreferrer"
                  >Follow on TikTok</a
                >
              </div>
            </div>
          </div>
        </section>
        <!-- End Salon & Hairdressing Section -->
        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Pricing Section -->
        <section
          class="page-section bg-gradient-gray-light-1 bg-scroll light-content"
          id="pricing"
        >
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

        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- FAQ Section -->
        <section class="page-section z-index-1">
          <div class="container position-relative">
            <div class="row position-relative">
              <div class="col-md-6 col-lg-5 mb-md-50 mb-sm-30">
                <h3 class="section-title mb-30">Frequently Asked Questions</h3>

                <p class="text-gray mb-0">
                  At Crystal Services Limited, we understand that you may have
                  questions about our professional cleaning services. To help
                  you make informed decisions, we’ve compiled a list of
                  frequently asked questions to address common concerns. If your
                  question isn’t listed, feel free to contact us directly, and
                  we’ll be happy to assist you.
                </p>
              </div>

              <div class="col-md-6 offset-lg-1 pt-10 pt-sm-0">
                <!-- Accordion -->
                <dl class="toggle">
                  <dt>
                    <a href="#"
                      >What types of cleaning services do you offer?</a
                    >
                  </dt>
                  <dd class="black">
                    We provide a wide range of cleaning services, including
                    residential cleaning, office cleaning, commercial space
                    cleaning, deep cleaning, and post-construction cleaning. We
                    tailor our services to meet your specific needs, ensuring
                    your space stays spotless and welcoming.
                  </dd>

                  <dt>
                    <a href="#"
                      >How do you ensure the safety and security of my property
                      during cleaning?</a
                    >
                  </dt>
                  <dd class="black">
                    Our team is fully trained, insured, and background-checked
                    for your peace of mind. We take extra precautions to
                    safeguard your property, and our cleaning professionals
                    follow strict protocols to ensure both security and the
                    highest cleaning standards.
                  </dd>

                  <dt>
                    <a href="#"
                      >Do I need to provide cleaning supplies and equipment?</a
                    >
                  </dt>
                  <dd class="black">
                    No, we bring our own high-quality, eco-friendly cleaning
                    supplies and equipment. However, if you have specific
                    products you'd like us to use, we’re happy to accommodate
                    your preferences.
                  </dd>

                  <dt>
                    <a href="#"
                      >How can I schedule or reschedule a cleaning service?</a
                    >
                  </dt>
                  <dd class="black">
                    Scheduling or rescheduling a cleaning is easy. Simply
                    contact us via phone, email, or our website, and we’ll work
                    with you to find a convenient time. We offer flexible
                    scheduling options to fit your needs.
                  </dd>
                  <dt>
                    <a href="#"
                      >What is the cancellation policy for your cleaning
                      services?</a
                    >
                  </dt>
                  <dd class="black">
                    We understand that plans can change. If you need to cancel
                    or reschedule your appointment, please notify us at least 24
                    hours in advance. Cancellations made within less than 24
                    hours may be subject to a cancellation fee.
                  </dd>
                  <dt>
                    <a href="#">Are your cleaning services customizable?</a>
                  </dt>
                  <dd class="black">
                    Absolutely! We offer fully customizable cleaning plans based
                    on your preferences and specific needs. Whether you require
                    a one-time deep clean or ongoing scheduled services, we’ll
                    tailor our approach to ensure the best results for your home
                    or business.
                  </dd>
                </dl>
                <!-- End Accordion -->
              </div>
            </div>
          </div>
        </section>
        <!-- End FAQ Section -->
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

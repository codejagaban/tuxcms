@php($hero = $cmsSections->get('hero'))
@php($salonServices = $cmsSections->get('salon-services'))
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Salon &amp; Hairdressing in Worcester &mdash; Crystal Services Limited</title>
    <meta
      name="description"
      content="Crystal Salon offers braiding, sew-ins, dreadlocks, re-locking, relaxing, washing &amp; setting and natural weaving in Worcester &mdash; plus hair products and attachments."
    />
    <meta name="keywords" content="hairdressing Worcester, braiding Worcester, sew-in weave, dreadlocks, re-locking, relaxing, natural weaving, hair products, Crystal Salon" />
    <link rel="canonical" href="{{ rtrim(\App\Support\Site::url(), '/') }}/salon/" />
    <meta name="robots" content="index, follow" />
    <!-- Open Graph Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Crystal Services Limited" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:title" content="Salon &amp; Hairdressing in Worcester &mdash; Crystal Services Limited" />
    <meta property="og:description" content="Crystal Salon offers braiding, sew-ins, dreadlocks, re-locking, relaxing, washing &amp; setting and natural weaving in Worcester &mdash; plus hair products and attachments." />
    <meta property="og:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />
    <meta property="og:url" content="{{ rtrim(\App\Support\Site::url(), '/') }}/salon/" />
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Salon &amp; Hairdressing in Worcester &mdash; Crystal Services Limited" />
    <meta name="twitter:description" content="Crystal Salon offers braiding, sew-ins, dreadlocks, re-locking, relaxing, washing &amp; setting and natural weaving in Worcester &mdash; plus hair products and attachments." />
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
      "@type": "HairSalon",
      "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/salon/#salon",
      "name": "Crystal Salon & Hairdressing",
      "url": "{{ rtrim(\App\Support\Site::url(), '/') }}/salon/",
      "image": "{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/salon/salon-about.jpg",
      "telephone": "+447717299921",
      "email": "info@crystalservicesltd.co.uk",
      "description": "Family-run salon in Worcester offering braiding, sew-ins, dreadlocks, re-locking, relaxing, washing & setting and natural weaving, plus hair products and attachments.",
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
      "parentOrganization": {
        "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/#business"
      }
    },
    {
      "@type": "ItemList",
      "name": "Salon & hairdressing services",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "item": {
            "@type": "Service",
            "name": "Braiding",
            "description": "Box braids, cornrows, knotless and feed-in protective styles.",
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
            "name": "Sew-in",
            "description": "Sew-in weaves professionally installed on a braided foundation.",
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
            "name": "Dreadlocks",
            "description": "Starter locs, maintenance and styling.",
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
            "name": "Re-locking",
            "description": "Retwisting and re-locking of new growth.",
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
            "name": "Relaxing",
            "description": "Relaxer treatments with aftercare conditioning.",
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
            "name": "Washing & setting",
            "description": "Wash, deep condition and professional set.",
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
          "position": 7,
          "item": {
            "@type": "Service",
            "name": "Natural weaving",
            "description": "Natural-look weaves matched to your hair texture and colour.",
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
        <!-- Hero Section -->
        <section class="page-section bg-gradient-gray-light-2 overflow-hidden">
          <!-- Background Shape -->
          <div class="bg-shape-1 wow fadeIn">
            <img src="/themes/crystal/images/demo-fancy/bg-shape-1.svg" alt="" />
          </div>
          <!-- End Background Shape -->

          <div class="container position-relative pt-10 pt-sm-40 text-center">
            <div class="row">
              <div class="col-md-10 offset-md-1 col-lg-8 offset-lg-2">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  {{ $hero?->data['subheading'] ?? 'Crystal Salon & Hairdressing' }}
                </h2>

                <h1 class="hs-title-10 mb-10">
                  <span class="wow charsAnimIn" data-splitting="chars">
                    {{ $hero?->title ?? 'Beautiful hair made with care' }}
                  </span>
                </h1>

                <p class="section-descr mb-0 wow fadeIn" data-wow-delay="0.2s">
                  {{ $hero?->content ?? 'From protective styles to fresh treatments, our salon offers expert hairdressing for every look.' }}
                </p>
              </div>
            </div>
          </div>
        </section>
        <!-- End Hero Section -->

        <!-- Salon Services Section -->
        <section class="page-section" id="salon-services">
          <div class="container position-relative">
            <div class="row mb-60 mb-sm-40">
              <div
                class="col-md-10 offset-md-1 col-lg-8 offset-lg-2 text-center"
              >
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  Salon &amp; Hairdressing
                </h2>
                <h3 class="section-title mb-30 mb-sm-20 wow fadeInUp">
                  {{ $salonServices?->title ?? 'Our hairdressing services' }}
                </h3>
                <p
                  class="section-descr mb-0 wow fadeInUp"
                  data-wow-delay="0.06s"
                >
                  {{ $salonServices?->content ?? 'Alongside our cleaning services, we now offer professional hairdressing.' }}
                </p>
              </div>
            </div>

            <!-- Salon Services Grid -->
            <div class="row services-5-grid">
              <!-- Item -->
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
                        <a
                          href="/contact/?service=Braiding#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->

              <!-- Item -->
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
                        <a
                          href="/contact/?service=Sew-in#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->

              <!-- Item -->
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
                        <a
                          href="/contact/?service=Dreadlocks#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->

              <!-- Item -->
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
                        <a
                          href="/contact/?service=Re-locking#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->

              <!-- Item -->
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
                        <a
                          href="/contact/?service=Relaxing#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->

              <!-- Item -->
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
                        <a
                          href="/contact/?service=Washing%20%26%20setting#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->

              <!-- Item -->
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
                        <a
                          href="/contact/?service=Natural%20weaving#contact_form"
                          class="btn btn-mod btn-color btn-small btn-round btn-hover-anim me-1 mt-10 mb-xs-10"
                          ><span>Book this style</span></a
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- End Item -->
            </div>
            <!-- End Salon Services Grid -->
          </div>
        </section>
        <!-- End Salon Services Section -->

        <!-- Divider -->
        <hr class="mt-0 mb-0" />
        <!-- End Divider -->

        <!-- Products / TikTok Section -->
        <section class="page-section bg-gradient-gray-light-1 bg-scroll light-content">
          <div class="container position-relative">
            <div class="row justify-content-center">
              <div class="col-md-10 col-lg-8 text-center">
                <h2 class="section-caption-fancy mb-20 mb-xs-10">
                  Hair products &amp; attachments
                </h2>
                <h3 class="section-title mb-30 mb-sm-20 wow fadeInUp">
                  Shop hair products &amp; attachments
                </h3>
                <p
                  class="section-descr mb-40 mb-sm-20 wow fadeInUp"
                  data-wow-delay="0.06s"
                >
                  We also sell a wide range of hair treatments, products and
                  attachments. Follow us on TikTok to see what's available and
                  place an order.
                </p>
                <div class="wow fadeInUp" data-wow-delay="0.12s">
                  <a
                    href="#"
                    class="btn btn-mod btn-color btn-large btn-round btn-hover-anim me-1 mb-xs-10"
                    target="_blank"
                    rel="noopener noreferrer"
                    ><span>Follow on TikTok</span></a
                  >
                  <a
                    href="/contact/"
                    class="btn btn-mod btn-w btn-large btn-round mb-xs-10"
                    >Book an appointment</a
                  >
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- End Products / TikTok Section -->
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

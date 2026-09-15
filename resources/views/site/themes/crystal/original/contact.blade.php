@php($hero = $cmsSections->get('hero'))
@php($details = $cmsSections->get('details'))
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Contact Us &mdash; Crystal Services Limited</title>
    <meta
      name="description"
      content="Book a cleaning or salon appointment with Crystal Services Limited. Call +44 771 729 9921, email info@crystalservicesltd.co.uk or visit 56 Northfield Street, Worcester."
    />
    <meta name="keywords" content="contact Crystal Services Limited, book cleaning Worcester, book hair appointment Worcester" />
    <link rel="canonical" href="{{ rtrim(\App\Support\Site::url(), '/') }}/contact/" />
    <meta name="robots" content="index, follow" />
    <!-- Open Graph Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Crystal Services Limited" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:title" content="Contact Us &mdash; Crystal Services Limited" />
    <meta property="og:description" content="Book a cleaning or salon appointment with Crystal Services Limited. Call +44 771 729 9921, email info@crystalservicesltd.co.uk or visit 56 Northfield Street, Worcester." />
    <meta property="og:image" content="{{ rtrim(\App\Support\Site::url(), '/') }}/themes/crystal/images/intro/thumbnail.png" />
    <meta property="og:url" content="{{ rtrim(\App\Support\Site::url(), '/') }}/contact/" />
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Contact Us &mdash; Crystal Services Limited" />
    <meta name="twitter:description" content="Book a cleaning or salon appointment with Crystal Services Limited. Call +44 771 729 9921, email info@crystalservicesltd.co.uk or visit 56 Northfield Street, Worcester." />
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
      "@type": "ContactPage",
      "@id": "{{ rtrim(\App\Support\Site::url(), '/') }}/contact/",
      "url": "{{ rtrim(\App\Support\Site::url(), '/') }}/contact/",
      "name": "Contact Crystal Services Limited",
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
                    {{ $hero?->title ?? 'Contact Us' }}
                  </span>
                </h1>

                <p class="section-descr mb-0 wow fadeIn" data-wow-delay="0.2s">
                  {{ $hero?->content ?? 'We’re here to help with all your cleaning needs.' }}
                </p>
              </div>
            </div>
          </div>
        </section>
        <!-- End Home Section -->

        <!-- Contact Section -->
        <section class="page-section" id="contact">
          <div
            class="container position-relative wow fadeInUp"
            data-wow-delay="0"
          >
            <div class="row">
              <!-- Left Column -->
              <div
                class="col-lg-4 mb-md-50 mb-sm-30 position-relative z-index-1"
              >
                <h2 class="section-caption-fancy mb-20 mb-xs-10">{{ $details?->data['eyebrow'] ?? "Let's Talk" }}</h2>

                <h3 class="section-title mb-50 mb-sm-30">
                  {{ $details?->title ?? 'We’re open to talk to good people.' }}
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
                        56 Northfield Street, Worcester, Worcestershire, WR1 1NT.
                      </div>
                      <div>
                        <a
                          href="https://www.google.com/maps?q=56+Northfield+Street,+Worcester,+Worcestershire+WR1+1NT"
                          class="link-hover-anim"
                          data-link-animate="y"
                          rel="nofollow noopener"
                          target="_blank"
                          >See Map <i class="mi-arrow-right size-18"></i
                        ></a>
                      </div>
                    </div>
                    <!-- End Address -->

                    <!-- Email -->
                    <div class="contact-item mb-30 mb-sm-20">
                      <div class="ci-icon">
                        <i class="mi-email"></i>
                      </div>
                      <h4 class="ci-title visually-hidden">Our Email</h4>
                      <div class="ci-text">
                        info@crystalservicesltd.co.uk
                        support@crystalservicesltd.co.uk
                      </div>
                      <div>
                        <a
                          href="mailto:info@crystalservicesltd.co.uk"
                          class="link-hover-anim"
                          data-link-animate="y"
                          >Say Hello <i class="mi-arrow-right size-18"></i
                        ></a>
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
                        <a href="tel:+447717299921">+44 771 729 9921</a>
                        <div class="small">(Monday-Friday: 9am to 7pm)</div>
                      </div>
                      <div>
                        <a
                          href="tel:+447717299921"
                          class="link-hover-anim"
                          data-link-animate="y"
                          >Call now <i class="mi-arrow-right size-18"></i
                        ></a>
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
                    <img
                      src="/themes/crystal/images/demo-fancy/contact-section-image.png"
                      width="225"
                      height="250"
                      alt="" loading="lazy" />
                  </div>
                  <!-- End Decorative Image -->

                  <div
                    class="box-shadow round p-4 p-sm-5 bg-gradient-gray-light-1 bg-scroll"
                  >
                    <h4 class="h3 mb-30 form-title">Get in touch </h4>

                    <!-- Contact Form -->
                    <form
                      class="form contact-form"
                      id="contact_form"
                      onsubmit="handleFormSubmit(event)"
                    >
                      <div class="row">
                        <input
                          type="hidden"
                          name="access_key"
                          value="65e32145-fc60-4ad1-86a7-6398381cfc30"
                        />
                        <input
                          type="hidden"
                          name="subject"
                          value="-- New Service Request Form --"
                        />
                        <div class="mb-3">
                          <div id="success-message" class="d-none"></div>
                          <div id="error-message" class="d-none"></div>
                        </div>
                        <div class="col-md-6">
                          <!-- Name -->
                          <div class="form-group">
                            <label for="Name">Name</label>
                            <input
                              type="text"
                              name="Name"
                              id="Name"
                              class="input-md round form-control"
                              placeholder="Enter your name"
                              pattern=".{3,100}"
                              required
                              aria-required="true"
                            />
                          </div>
                          <!-- End Name -->
                        </div>

                        <div class="col-md-6">
                          <!-- Email -->
                          <div class="form-group">
                            <label for="Email Address">Email Address</label>
                            <input
                              type="email"
                              name="Email Address"
                              id="Email Address"
                              class="input-md round form-control"
                              placeholder="Enter your email"
                              required
                              aria-required="true"
                            />
                          </div>
                          <!-- End Email -->
                        </div>
                      </div>

                      <!-- phone -->
                      <div class="form-group">
                        <label for="Phone Number">Phone Number</label>
                        <input
                          type="text"
                          name="Phone Number"
                          id="Phone Number"
                          class="input-md round form-control"
                          placeholder="+44 9028 XXXXX"
                          required
                          aria-required="true"
                        />
                      </div>

                      <!-- services -->
                      <div class="form-group">
                        <label for="service">Select a service</label>
                        <select
                          name="Service"
                          id="Service"
                          class="input-md round form-control"
                          required=""
                        >
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
                        <textarea
                          name="message"
                          id="message"
                          class="input-md round form-control"
                          style="height: 130px"
                          placeholder="Enter your message"
                        ></textarea>
                      </div>

                      <div class="row">
                        <div class="col-md-6 col-xl-5">
                          <!-- Send Button -->
                          <div class="pt-3">
                            <button
                              class="submit_btn btn btn-mod btn-gray btn-large btn-round btn-hover-anim"
                              id="submit_btn"
                              aria-controls="result"
                            >
                              <span>Send Message</span>
                            </button>
                          </div>
                          <!-- End Send Button -->
                        </div>
                      </div>

                      <div
                        id="result"
                        role="region"
                        aria-live="polite"
                        aria-atomic="true"
                      ></div>
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
        <!-- Map Section -->
        <div class="gmap_canvas contact_map">
          <iframe
            src="https://www.google.com/maps?q=56%20Northfield%20Street%2C%20Worcester%2C%20Worcestershire%20WR1%201NT&output=embed"
            width="800"
            height="600"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
        </div>
        <!-- End Map Section -->
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

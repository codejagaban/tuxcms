<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\Seo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/** Import the Crystal Services website into the Crystal TuxCMS theme. */
class CrystalContentSeeder extends Seeder
{
    private User $author;

    public function run(): void
    {
        $this->author = User::first()
            ?? throw new RuntimeException('Create an editor with `php artisan user:create` before importing Crystal.');

        $this->seedSettings();
        $this->wipePages();
        $this->seedPages();

        $this->command?->info('Crystal content seeded: 5 pages.');
    }

    private function seedSettings(): void
    {
        $settings = [
            'site_name' => ['Crystal Services Limited', 'site'],
            'site_tagline' => ['Experience the Crystal Service touch, from the first sparkle to the lasting freshness of a perfectly clean, welcoming space.', 'site'],
            'site_description' => ['Family-run Worcester business offering professional cleaning services and salon hairdressing.', 'site'],
            'site_logo' => ['/themes/crystal/images/logo/logo.svg', 'site'],
            'default_og_image' => ['/themes/crystal/images/intro/thumbnail.png', 'seo'],
            'contact_email' => ['info@crystalservicesltd.co.uk', 'contact'],
            'contact_phone' => ['+44 771 729 9921', 'contact'],
            'contact_address' => ['56 Northfield Street, Worcester, Worcestershire, WR1 1NT', 'contact'],
            'web3forms_access_key' => ['65e32145-fc60-4ad1-86a7-6398381cfc30', 'forms'],
        ];

        foreach ($settings as $key => [$value, $group]) {
            Setting::set($key, $value, $group);
        }
    }

    private function wipePages(): void
    {
        foreach (Page::withTrashed()->get() as $page) {
            Seo::where('seoable_type', Page::class)->where('seoable_id', $page->id)->delete();
            $page->sections()->get()->each->delete();
            $page->forceDelete();
        }
    }

    private function seedPages(): void
    {
        $this->makePage('Home', true, 0, [
            $this->section('hero', 'hero', 'A crystal cleaning you can trust', "We proudly provide unparalleled cleaning services across the UK. Our mission is to consistently exceed our clients' expectations with reliable, high-quality cleaning solutions.", [
                'subheading' => 'Your trusted cleaning partner',
                'image' => '/themes/crystal/images/intro/hero.png',
                'image_alt' => 'Professional cleaner from Crystal Services Limited',
                'primary_cta' => ['label' => 'View services', 'url' => '/services/'],
                'secondary_cta' => ['label' => 'Book a cleaning', 'url' => '/contact/'],
            ]),
            $this->section('about', 'text', 'Your trusted partner for sparkling clean homes and offices.', 'At Crystal Service Limited, we believe that a clean environment leads to a better quality of life. Established with a commitment to delivering high-quality cleaning services across the UK, we specialize in a range of services including commercial cleaning, domestic cleaning, and more. Whether it’s a sparkling workspace or a spotless home, our dedicated team is trained to meet the highest standards of cleanliness.', [
                'eyebrow' => 'About us',
                'image' => '/themes/crystal/images/services/airbnb-cleaning.webp',
                'image_alt' => 'Crystal Services cleaner preparing an Airbnb property',
            ]),
            $this->section('services', 'features', 'Professional cleaning services tailored to your needs', null, [
                'eyebrow' => 'Our services',
                'items' => $this->homeCleaningServices(),
            ]),
            $this->section('salon', 'features', 'Now offering professional hairdressing', 'Alongside our cleaning services, we now offer professional hairdressing. Book a style or shop hair products and attachments.', [
                'eyebrow' => 'Salon & Hairdressing',
                'items' => $this->homeSalonServices(),
            ]),
            $this->section('testimonials', 'testimonials', 'Trusted by Homes and Businesses Across the UK', 'Hear directly from the individuals and businesses who have experienced the Crystal Service difference—exceptional cleaning results, professional teams, and reliable service.', [
                'eyebrow' => 'Testimonials',
                'items' => [
                    ['quote' => 'We’ve been using Crystal Service Limited for our office cleaning, and their attention to detail is outstanding. The team is always punctual, and the place looks spotless after every visit. Highly recommend!', 'author' => 'John M.', 'role' => 'Commercial Client'],
                    ['quote' => 'I needed an end-of-tenancy clean, and Crystal Services came to the rescue. They were professional, worked quickly, and left the place looking brand new. Got my deposit back without a hitch!', 'author' => 'Sarah P', 'role' => 'Tenant'],
                    ['quote' => 'The deep cleaning service was a game-changer for our home. The team was friendly, efficient, and left every corner of our house looking spotless. We now use them regularly for our domestic cleaning.', 'author' => 'Emily R.', 'role' => 'Homeowner'],
                    ['quote' => 'As an Airbnb host, cleanliness is a top priority for me. Crystal Service Limited provides timely and thorough cleans between guests, which has improved my guest reviews significantly. Highly reliable!', 'author' => 'David A.', 'role' => 'Airbnb Host'],
                ],
            ]),
            $this->section('contact', 'cta', 'Book us today for a professional cleaning and enjoy a spotless space. Fast, and reliable service tailored to your needs.', null, ['eyebrow' => 'Let Us Handle the Dirty Work', 'primary_cta' => ['label' => 'Book a cleaning session', 'url' => '/contact/']]),
        ], $this->seo(
            'Crystal Services Limited — Cleaning & Salon Services in Worcester',
            'Professional cleaning and salon hairdressing in Worcester. Commercial, deep and end-of-tenancy cleaning plus braiding, sew-ins and hair care.'
        ));

        $this->makePage('About', false, 1, [
            $this->section('hero', 'hero', 'About us', "Learn about us, our commitment to quality, and why we're the best choice for your cleaning and hairdressing needs", ['subheading' => 'About Crystal Services', 'image' => '/themes/crystal/images/about/hero-banner.webp']),
            $this->section('story', 'text', 'About Crystal Services', "At Crystal Service Limited, we believe that a clean environment leads to a better quality of life. Established with a commitment to delivering high-quality cleaning services across the UK, we specialize in a range of services including commercial cleaning, domestic cleaning, and more. Whether it’s a sparkling workspace or a spotless home, our dedicated team is trained to meet the highest standards of cleanliness. And we're no longer just about cleaning — through Crystal Salon, our family-run hairdressing business, we bring that same care to your hair too.", ['eyebrow' => 'Our story', 'image' => '/themes/crystal/images/about/1.jpg', 'image_alt' => 'The Crystal Services team']),
            $this->section('values', 'features', 'What matters to us', null, ['items' => [
                ['title' => 'Reliable service', 'description' => 'We arrive prepared, communicate clearly and do what we promise.', 'image' => '/themes/crystal/images/about/services-6.png'],
                ['title' => 'Careful work', 'description' => 'Every home, workplace and client receives close attention.', 'image' => '/themes/crystal/images/about/services-0.png'],
                ['title' => 'Local relationships', 'description' => 'We are proud to build lasting relationships across Worcester.', 'image' => '/themes/crystal/images/about/services-5.png'],
            ]]),
            $this->section('contact', 'cta', 'How can we help?', 'Speak with our team about cleaning or salon services.', ['primary_cta' => ['label' => 'Get in touch', 'url' => '/contact/']]),
        ], $this->seo('About Us — Crystal Services Limited, Worcester', 'Meet the family-run Worcester business behind Crystal Services Limited cleaning and salon services.'));

        $this->makePage('Services', false, 2, [
            $this->section('hero', 'hero', 'Our Services', 'We provide a wide range of professional cleaning services tailored to meet the needs of both residential and commercial clients. No matter the size or scope of the job, our expert cleaners ensure every space is left spotless, fresh, and inviting. Here’s a closer look at the services we offer:', ['subheading' => 'Professional cleaning in Worcester', 'image' => '/themes/crystal/images/services/feature-2.webp']),
            $this->section('services', 'features', 'Our cleaning services', null, ['items' => $this->cleaningServices()]),
            $this->section('process', 'features', 'A straightforward process', null, ['items' => [
                ['title' => 'Tell us what you need', 'description' => 'Share the property, schedule and priorities with our team.'],
                ['title' => 'Receive a tailored quote', 'description' => 'We provide a clear plan and no-obligation price.'],
                ['title' => 'We take care of the work', 'description' => 'Our trained cleaners arrive equipped and ready.'],
            ]]),
            $this->section('book', 'cta', 'Let us handle the dirty work', 'Book a professional clean and enjoy a spotless space.', ['primary_cta' => ['label' => 'Book a cleaning', 'url' => '/contact/']]),
        ], $this->seo('Cleaning Services in Worcester — Crystal Services Limited', 'Commercial, deep, end-of-tenancy, Airbnb, upholstery and after-builders cleaning in Worcester.'));

        $this->makePage('Salon', false, 3, [
            $this->section('hero', 'hero', 'Beautiful hair made with care', 'From protective styles to fresh treatments, our salon offers expert hairdressing for every look — plus quality hair products and attachments.', ['subheading' => 'Crystal Salon & Hairdressing', 'image' => '/themes/crystal/images/salon/salon-about.jpg']),
            $this->section('salon-services', 'features', 'Our hairdressing services', 'Alongside our cleaning services, we now offer professional hairdressing. Book a style or shop hair products and attachments.', ['items' => $this->salonServices()]),
            $this->section('book', 'cta', 'Ready to book your appointment?', 'Tell us the style or treatment you have in mind.', ['primary_cta' => ['label' => 'Book an appointment', 'url' => '/contact/?service=Salon#contact_form']]),
        ], $this->seo('Salon & Hairdressing in Worcester — Crystal Services Limited', 'Braiding, sew-ins, dreadlocks, re-locking, relaxing and natural hair care from Crystal Salon in Worcester.'));

        $services = collect($this->cleaningServices())->merge($this->salonServices())->pluck('title')->values()->all();
        $this->makePage('Contact', false, 4, [
            $this->section('hero', 'hero', 'Contact Us', 'We’re here to help with all your cleaning needs. Whether you have questions, need a quote, or want to book a service, our friendly team is ready to assist you. Reach out to us and let’s get started on making your space sparkle!', ['subheading' => 'Contact Crystal Services', 'image' => '/themes/crystal/images/demo-fancy/contact-section-image.png']),
            $this->section('details', 'contact', 'We’re open to talk to good people.', 'Call, email or visit us in Worcester.', ['eyebrow' => "Let's Talk", 'image' => '/themes/crystal/images/services/about-4.webp', 'image_alt' => 'Crystal Services team at work']),
            $this->section('contact-form', 'contact_form', 'Send us a message', 'Choose a service and tell us how we can help.', ['subject' => 'New Crystal Services website enquiry', 'button_label' => 'Send message', 'services' => $services]),
            $this->section('map', 'map', 'Find us', null, ['query' => '56 Northfield Street, Worcester, Worcestershire, WR1 1NT', 'label' => 'Crystal Services Limited, Worcester']),
        ], $this->seo('Contact Us — Crystal Services Limited', 'Book cleaning or a salon appointment with Crystal Services Limited in Worcester.'));
    }

    private function cleaningServices(?int $limit = null): array
    {
        $items = [
            ['title' => 'Commercial Cleaning', 'description' => 'We understand the importance of a clean and organized workspace for productivity and employee well-being. Our commercial cleaning service caters to offices, retail spaces, and other commercial properties. We work around your schedule to ensure minimal disruption, providing daily, weekly, or one-time deep cleans.', 'image' => '/themes/crystal/images/about/services-6.png'],
            ['title' => 'Upholstery Cleaning', 'description' => 'Our upholstery cleaning service uses advanced techniques to remove stains, dirt, and allergens from your furniture, leaving it looking fresh and extending its lifespan. Whether it’s fabric or leather, our team carefully handles each piece to maintain its quality.', 'image' => '/themes/crystal/images/about/services-3.png'],
            ['title' => 'End-of-Tenancy Cleaning', 'description' => 'Moving out? Our end-of-tenancy cleaning service is designed to help tenants leave their property in pristine condition, ensuring the return of their security deposit. We clean every corner of the property, including carpets, bathrooms, kitchens, and windows, to meet landlord or agent expectations.', 'image' => '/themes/crystal/images/about/services-0.png'],
            ['title' => 'Airbnb Cleaning', 'description' => 'Make a lasting impression on your guests with our specialized Airbnb cleaning service. We provide quick and thorough cleans between guest stays, ensuring your property is always guest-ready with fresh linens, clean bathrooms, and a spotless living area.', 'image' => '/themes/crystal/images/about/services-2.png'],
            ['title' => 'Deep Cleaning', 'description' => 'Our deep cleaning service is perfect for homes or offices that need more than just a surface clean. We tackle those hard-to-reach areas, scrubbing floors, disinfecting surfaces, and removing built-up grime from kitchens, bathrooms, and other high-traffic spaces.', 'image' => '/themes/crystal/images/about/services-5.png'],
            ['title' => 'After Builders Cleaning', 'description' => 'Renovations can leave a mess behind. Our after-builders cleaning service ensures that your property is spotless once construction or renovations are completed. We remove dust, debris, and other post-construction residues, leaving your space ready for use.', 'image' => '/themes/crystal/images/services/project-single-1.jpg'],
        ];

        return array_map(fn ($item) => $item + ['url' => '/contact/?service='.rawurlencode($item['title']).'#contact_form', 'link_label' => 'Book this service'], $limit ? array_slice($items, 0, $limit) : $items);
    }

    private function homeCleaningServices(): array
    {
        return [
            ['title' => 'Commercial Cleaning', 'description' => 'Offices, retail and commercial spaces, cleaned around your schedule.', 'image' => '/themes/crystal/images/about/services-6.png'],
            ['title' => 'Deep Cleaning', 'description' => 'A thorough top-to-bottom clean for homes and offices.', 'image' => '/themes/crystal/images/about/services-5.png'],
            ['title' => 'End-of-Tenancy Cleaning', 'description' => 'Move out with your deposit — every corner left pristine.', 'image' => '/themes/crystal/images/about/services-0.png'],
        ];
    }

    private function salonServices(?int $limit = null): array
    {
        $items = [
            ['title' => 'Braiding', 'description' => 'From box braids and cornrows to knotless and feed-in styles, our braiding is done with care and precision. We keep tension gentle on your scalp so every braid is neat, comfortable and protective — a style that lasts for weeks while keeping your natural hair healthy underneath.', 'image' => '/themes/crystal/images/salon/braiding.jpg'],
            ['title' => 'Sew-in', 'description' => 'Our sew-in weaves are professionally installed on a secure braided foundation for a flawless, natural-looking finish. Whether you want extra length, fuller volume or a whole new look, we blend the weave seamlessly with your own hair and make sure it sits comfortably from day one.', 'image' => '/themes/crystal/images/salon/sew-in.jpg'],
            ['title' => 'Dreadlocks', 'description' => 'Starting your loc journey or maintaining mature locs? We offer starter locs, root maintenance and styling, all done with healthy hair practices. We help you choose the right size and pattern for your hair type, and keep your locs clean, strong and well-shaped as they grow.', 'image' => '/themes/crystal/images/salon/dreadlocks.jpg'],
            ['title' => 'Re-locking', 'description' => 'Regular retwisting keeps your locs looking sharp. We carefully re-lock the new growth at your roots so every loc stays neat and uniform, without over-twisting — protecting your edges and encouraging strong, healthy growth between appointments.', 'image' => '/themes/crystal/images/salon/re-locking.jpg'],
            ['title' => 'Relaxing', 'description' => 'Our relaxer treatments are applied with care to smooth and soften your natural texture while keeping your hair healthy. We assess your hair first, choose the right strength for you, and finish with a deep condition, leaving your hair silky, manageable and full of movement.', 'image' => '/themes/crystal/images/salon/relaxing.jpg'],
            ['title' => 'Washing & setting', 'description' => 'A thorough cleanse, deep condition and professional set, tailored to your hair. Whether you prefer rollers, a sleek blow-dry or a bouncy curled finish, you leave with fresh, healthy-looking hair that holds its shape for days.', 'image' => '/themes/crystal/images/salon/washing-and-setting.jpg'],
            ['title' => 'Natural weaving', 'description' => 'Our natural weaving blends quality extensions with your own hair for a look that is full, flowing and completely believable. We match texture and colour carefully and install with a technique that protects your natural hair, so you get a beautiful style without the damage.', 'image' => '/themes/crystal/images/salon/natural-weaving.jpg'],
        ];

        return array_map(fn ($item) => $item + ['url' => '/contact/?service='.rawurlencode($item['title']).'#contact_form', 'link_label' => 'Book this style'], $limit ? array_slice($items, 0, $limit) : $items);
    }

    private function homeSalonServices(): array
    {
        return [
            ['title' => 'Braiding', 'description' => 'Box braids, cornrows and protective styles that last.', 'image' => '/themes/crystal/images/salon/braiding.jpg'],
            ['title' => 'Sew-in', 'description' => 'Natural-looking weaves, professionally installed.', 'image' => '/themes/crystal/images/salon/sew-in.jpg'],
            ['title' => 'Dreadlocks', 'description' => 'Starter locs, maintenance and styling.', 'image' => '/themes/crystal/images/salon/dreadlocks.jpg'],
        ];
    }

    private function makePage(string $title, bool $homepage, int $order, array $sections, array $seo): Page
    {
        $page = Page::create([
            'title' => $title,
            'template' => 'crystal',
            'status' => 'published',
            'is_homepage' => $homepage,
            'show_in_nav' => true,
            'order' => $order,
            'author_id' => $this->author->id,
            'published_at' => now(),
        ]);

        foreach ($sections as $index => $section) {
            PageSection::create($section + ['page_id' => $page->id, 'order' => $index, 'is_visible' => true]);
        }

        $page->seo()->create($seo);

        return $page;
    }

    private function section(string $key, string $type, ?string $title, ?string $content = null, array $data = []): array
    {
        return compact('key', 'type', 'title', 'content', 'data');
    }

    private function seo(string $title, string $description): array
    {
        return ['meta_title' => $title, 'meta_description' => $description, 'og_type' => 'website', 'robots' => 'index, follow'];
    }
}

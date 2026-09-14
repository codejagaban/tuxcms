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
            $this->section('hero', 'hero', 'A crystal cleaning you can trust', 'We provide dependable, high-quality cleaning services across Worcester and the surrounding area.', [
                'subheading' => 'Your trusted cleaning partner',
                'image' => '/themes/crystal/images/intro/hero.png',
                'image_alt' => 'Professional cleaner from Crystal Services Limited',
                'primary_cta' => ['label' => 'View services', 'url' => '/services/'],
                'secondary_cta' => ['label' => 'Book a cleaning', 'url' => '/contact/'],
            ]),
            $this->section('about', 'text', 'Your trusted partner for sparkling clean homes and offices.', 'At Crystal Services Limited, we believe that a clean environment leads to a better quality of life. Our trained team delivers commercial, domestic and specialist cleaning to exacting standards.', [
                'eyebrow' => 'About us',
                'image' => '/themes/crystal/images/services/airbnb-cleaning.webp',
                'image_alt' => 'Crystal Services cleaner preparing an Airbnb property',
            ]),
            $this->section('services', 'features', 'Professional cleaning services tailored to your needs', null, [
                'eyebrow' => 'Our services',
                'items' => $this->cleaningServices(3),
            ]),
            $this->section('salon', 'features', 'Salon and hairdressing in Worcester', 'Braiding, sew-ins, dreadlocks, re-locking and healthy hair care from an experienced local team.', [
                'eyebrow' => 'Crystal Salon',
                'items' => $this->salonServices(3),
            ]),
            $this->section('testimonials', 'testimonials', 'What our customers say', null, [
                'items' => [
                    ['quote' => 'Reliable, thorough and always friendly. Our offices have never looked better.', 'author' => 'Commercial cleaning client', 'role' => 'Worcester'],
                    ['quote' => 'The salon team listened carefully and the result was exactly what I wanted.', 'author' => 'Salon customer', 'role' => 'Worcester'],
                ],
            ]),
            $this->section('contact', 'cta', 'Ready for a cleaner space or a fresh new look?', 'Tell us what you need and we will get back to you with the right service.', ['eyebrow' => 'Get started', 'primary_cta' => ['label' => 'Contact us', 'url' => '/contact/']]),
        ], $this->seo(
            'Crystal Services Limited — Cleaning & Salon Services in Worcester',
            'Professional cleaning and salon hairdressing in Worcester. Commercial, deep and end-of-tenancy cleaning plus braiding, sew-ins and hair care.'
        ));

        $this->makePage('About', false, 1, [
            $this->section('hero', 'hero', 'A family-run business built on care', 'Two complementary services, one commitment to quality and personal service.', ['subheading' => 'About Crystal Services', 'image' => '/themes/crystal/images/about/hero-banner.webp']),
            $this->section('story', 'text', 'Cleaning and care you can rely on', "Crystal Services Limited serves homes and businesses across Worcester with professional cleaning and salon hairdressing.\n\nWe believe good service starts with listening, continues with careful work, and ends only when our customer is satisfied.", ['eyebrow' => 'Our story', 'image' => '/themes/crystal/images/about/1.jpg', 'image_alt' => 'The Crystal Services team']),
            $this->section('values', 'features', 'What matters to us', null, ['items' => [
                ['title' => 'Reliable service', 'description' => 'We arrive prepared, communicate clearly and do what we promise.', 'image' => '/themes/crystal/images/about/services-6.png'],
                ['title' => 'Careful work', 'description' => 'Every home, workplace and client receives close attention.', 'image' => '/themes/crystal/images/about/services-0.png'],
                ['title' => 'Local relationships', 'description' => 'We are proud to build lasting relationships across Worcester.', 'image' => '/themes/crystal/images/about/services-5.png'],
            ]]),
            $this->section('contact', 'cta', 'How can we help?', 'Speak with our team about cleaning or salon services.', ['primary_cta' => ['label' => 'Get in touch', 'url' => '/contact/']]),
        ], $this->seo('About Us — Crystal Services Limited, Worcester', 'Meet the family-run Worcester business behind Crystal Services Limited cleaning and salon services.'));

        $this->makePage('Services', false, 2, [
            $this->section('hero', 'hero', 'Cleaning services for every kind of space', 'From recurring commercial cleaning to one-off deep cleans, we tailor the work to your property.', ['subheading' => 'Professional cleaning in Worcester', 'image' => '/themes/crystal/images/services/feature-2.webp']),
            $this->section('services', 'features', 'Choose the service you need', null, ['items' => $this->cleaningServices()]),
            $this->section('process', 'features', 'A straightforward process', null, ['items' => [
                ['title' => 'Tell us what you need', 'description' => 'Share the property, schedule and priorities with our team.'],
                ['title' => 'Receive a tailored quote', 'description' => 'We provide a clear plan and no-obligation price.'],
                ['title' => 'We take care of the work', 'description' => 'Our trained cleaners arrive equipped and ready.'],
            ]]),
            $this->section('book', 'cta', 'Let us handle the dirty work', 'Book a professional clean and enjoy a spotless space.', ['primary_cta' => ['label' => 'Book a cleaning', 'url' => '/contact/']]),
        ], $this->seo('Cleaning Services in Worcester — Crystal Services Limited', 'Commercial, deep, end-of-tenancy, Airbnb, upholstery and after-builders cleaning in Worcester.'));

        $this->makePage('Salon', false, 3, [
            $this->section('hero', 'hero', 'Hair care shaped around you', 'Protective styling, natural hair care and dependable salon service in Worcester.', ['subheading' => 'Crystal Salon & Hairdressing', 'image' => '/themes/crystal/images/salon/salon-about.jpg']),
            $this->section('salon-services', 'features', 'Salon services', null, ['items' => $this->salonServices()]),
            $this->section('book', 'cta', 'Ready to book your appointment?', 'Tell us the style or treatment you have in mind.', ['primary_cta' => ['label' => 'Book an appointment', 'url' => '/contact/?service=Salon#contact_form']]),
        ], $this->seo('Salon & Hairdressing in Worcester — Crystal Services Limited', 'Braiding, sew-ins, dreadlocks, re-locking, relaxing and natural hair care from Crystal Salon in Worcester.'));

        $services = collect($this->cleaningServices())->merge($this->salonServices())->pluck('title')->values()->all();
        $this->makePage('Contact', false, 4, [
            $this->section('hero', 'hero', 'Let’s talk about what you need', 'Request a cleaning quote or book a salon appointment with our Worcester team.', ['subheading' => 'Contact Crystal Services', 'image' => '/themes/crystal/images/demo-fancy/contact-section-image.png']),
            $this->section('details', 'contact', 'We’re here to help', 'Call, email or visit us in Worcester.', ['eyebrow' => 'Contact us', 'image' => '/themes/crystal/images/services/about-4.webp', 'image_alt' => 'Crystal Services team at work']),
            $this->section('contact-form', 'contact_form', 'Send us a message', 'Choose a service and tell us how we can help.', ['subject' => 'New Crystal Services website enquiry', 'button_label' => 'Send message', 'services' => $services]),
            $this->section('map', 'map', 'Find us', null, ['query' => '56 Northfield Street, Worcester, Worcestershire, WR1 1NT', 'label' => 'Crystal Services Limited, Worcester']),
        ], $this->seo('Contact Us — Crystal Services Limited', 'Book cleaning or a salon appointment with Crystal Services Limited in Worcester.'));
    }

    private function cleaningServices(?int $limit = null): array
    {
        $items = [
            ['title' => 'Commercial cleaning', 'description' => 'Reliable cleaning for offices, shops and shared workplaces.', 'image' => '/themes/crystal/images/about/services-6.png'],
            ['title' => 'Carpet & upholstery cleaning', 'description' => 'Deep treatment for carpets, sofas and upholstered furnishings.', 'image' => '/themes/crystal/images/about/services-3.png'],
            ['title' => 'End-of-tenancy cleaning', 'description' => 'A comprehensive clean ready for inspection or new occupants.', 'image' => '/themes/crystal/images/about/services-0.png'],
            ['title' => 'Airbnb cleaning', 'description' => 'Fast, consistent changeovers between guest stays.', 'image' => '/themes/crystal/images/about/services-2.png'],
            ['title' => 'Deep cleaning', 'description' => 'Detailed top-to-bottom cleaning for homes and workplaces.', 'image' => '/themes/crystal/images/about/services-5.png'],
            ['title' => 'After-builders cleaning', 'description' => 'Remove dust and residue after renovation or construction.', 'image' => '/themes/crystal/images/services/project-single-1.jpg'],
        ];

        return array_map(fn ($item) => $item + ['url' => '/contact/?service='.rawurlencode($item['title']).'#contact_form', 'link_label' => 'Book this service'], $limit ? array_slice($items, 0, $limit) : $items);
    }

    private function salonServices(?int $limit = null): array
    {
        $items = [
            ['title' => 'Braiding', 'description' => 'Neat, protective braids tailored to your preferred finish.', 'image' => '/themes/crystal/images/salon/braiding.jpg'],
            ['title' => 'Sew-in', 'description' => 'Secure sew-in installations with a natural-looking result.', 'image' => '/themes/crystal/images/salon/sew-in.jpg'],
            ['title' => 'Dreadlocks', 'description' => 'Starter locs, maintenance and styling with careful technique.', 'image' => '/themes/crystal/images/salon/dreadlocks.jpg'],
            ['title' => 'Re-locking', 'description' => 'Root maintenance that keeps existing locs tidy and healthy.', 'image' => '/themes/crystal/images/salon/re-locking.jpg'],
            ['title' => 'Relaxing', 'description' => 'Professional relaxing with attention to hair and scalp health.', 'image' => '/themes/crystal/images/salon/relaxing.jpg'],
            ['title' => 'Natural weaving', 'description' => 'Versatile woven styles designed around your natural hair.', 'image' => '/themes/crystal/images/salon/natural-weaving.jpg'],
        ];

        return array_map(fn ($item) => $item + ['url' => '/contact/?service='.rawurlencode($item['title']).'#contact_form', 'link_label' => 'Book this style'], $limit ? array_slice($items, 0, $limit) : $items);
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

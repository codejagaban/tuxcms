<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\Seo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a realistic business website: pages with builder sections and SEO.
 *
 * Idempotent — re-running wipes the demo pages and rebuilds them, so it never
 * accumulates duplicates.
 *
 *   php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    private User $author;

    public function run(): void
    {
        $this->author = User::firstOrCreate(
            ['email' => 'admin@tuxcms.com'],
            ['name' => 'Site Admin', 'password' => Hash::make('password')]
        );

        $this->wipeExistingPages();
        $pages = $this->seedPages();

        $this->command?->info('Demo content seeded: ' . count($pages) . ' pages.');
        $this->command?->info('Log in as admin@tuxcms.com to edit it.');
    }

    /** Remove previously seeded pages so the seeder is safe to re-run. */
    private function wipeExistingPages(): void
    {
        foreach (Page::withTrashed()->get() as $page) {
            Seo::where('seoable_type', Page::class)->where('seoable_id', $page->id)->delete();
            $page->sections()->delete();
            $page->forceDelete();
        }
    }

    /** @return array<int, Page> */
    private function seedPages(): array
    {
        $home = $this->makePage([
            'title' => 'Home',
            'template' => 'default',
            'status' => 'published',
            'is_homepage' => true,
            'show_in_nav' => true,
            'order' => 0,
            'excerpt' => 'Lumen Studio designs and builds digital products people actually enjoy using.',
            'content' => 'Lumen Studio is a compact product team combining design, engineering and strategy.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'We design & build products that earn their keep',
                'content' => 'From first sketch to production, Lumen Studio ships polished web apps, brand systems and marketing sites for teams that care about the details.',
                'data' => [
                    'subheading' => 'A product studio for founders and growing teams',
                    'primary_cta' => ['label' => 'Start a project', 'url' => '/contact/'],
                    'secondary_cta' => ['label' => 'See our work', 'url' => '/services/'],
                    'background_image' => '',
                    'alignment' => 'center',
                ],
            ],
            [
                'key' => 'features', 'type' => 'features', 'title' => 'What we do',
                'content' => 'Three tightly connected practices, one accountable team.',
                'data' => [
                    'columns' => 3,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Product design', 'description' => 'Research, UX and interface design that turns fuzzy ideas into shippable flows.'],
                        ['icon' => 'code', 'title' => 'Engineering', 'description' => 'Reliable front-end and full-stack builds with a bias for performance and maintainability.'],
                        ['icon' => 'sparkles', 'title' => 'Brand & identity', 'description' => 'Logos, systems and guidelines that make a product feel like a company.'],
                    ],
                ],
            ],
            [
                'key' => 'stats', 'type' => 'stats', 'title' => 'A decade of shipping',
                'data' => [
                    'items' => [
                        ['label' => 'Products launched', 'value' => '120+'],
                        ['label' => 'Avg. client rating', 'value' => '4.9/5'],
                        ['label' => 'Years in business', 'value' => '11'],
                        ['label' => 'Team members', 'value' => '9'],
                    ],
                ],
            ],
            [
                'key' => 'testimonials', 'type' => 'testimonials', 'title' => 'What clients say',
                'data' => [
                    'items' => [
                        ['quote' => 'Lumen felt like part of our team from week one. The redesign paid for itself in a quarter.', 'author' => 'Priya Nair', 'role' => 'Head of Product, Fathom'],
                        ['quote' => 'Rare to find design and engineering this aligned. They just ship.', 'author' => 'Marcus Lee', 'role' => 'Founder, Overlap'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'Have something in mind?',
                'content' => 'Tell us about your project and we will get back within a day.',
                'data' => ['primary_cta' => ['label' => 'Get in touch', 'url' => '/contact/']],
            ],
        ], [
            'meta_title' => 'Lumen Studio — Product design & engineering',
            'meta_description' => 'Lumen Studio designs and builds web apps, brand systems and marketing sites for founders and growing teams.',
            'meta_keywords' => 'product studio, web design, ux, engineering',
            'og_title' => 'Lumen Studio',
            'og_description' => 'Design & engineering for growing products.',
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'robots' => 'index, follow',
        ]);

        $about = $this->makePage([
            'title' => 'About',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 1,
            'excerpt' => 'A small, senior team that treats your product like our own.',
            'content' => 'We started Lumen in 2014 with a simple idea: keep the team small, keep the quality high.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Small team, senior work',
                'content' => 'No account managers, no handoffs to juniors. You work directly with the people doing the work.',
                'data' => ['alignment' => 'left', 'background_image' => ''],
            ],
            [
                'key' => 'story', 'type' => 'text', 'title' => 'Our story',
                'content' => "Lumen Studio began in 2014 as two designers and an engineer sharing a desk. Eleven years on we're a team of nine, but the model hasn't changed: small pods, senior people, and a direct line to whoever is building your product.\n\nWe work in tight loops — design and engineering in the same room — so decisions get made quickly and nothing gets lost in translation.",
            ],
            [
                'key' => 'team', 'type' => 'team', 'title' => 'The people',
                'data' => [
                    'members' => [
                        ['name' => 'Ada Okafor', 'role' => 'Principal Designer', 'bio' => 'Leads product and brand work. Ex-agency, ex-startup.'],
                        ['name' => 'Tom Reyes', 'role' => 'Engineering Lead', 'bio' => 'Full-stack. Cares far too much about load times.'],
                        ['name' => 'Sofia Bianchi', 'role' => 'Strategy', 'bio' => 'Turns business goals into a roadmap that ships.'],
                    ],
                ],
            ],
        ], [
            'meta_title' => 'About — Lumen Studio',
            'meta_description' => 'Meet the small, senior team behind Lumen Studio.',
            'og_type' => 'website',
            'robots' => 'index, follow',
        ]);

        $services = $this->makePage([
            'title' => 'Services',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 2,
            'excerpt' => 'Design, engineering and brand — as a package or à la carte.',
            'content' => 'Engagements from a two-week design sprint to a full product build.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Services',
                'content' => 'Pick a single discipline or the full stack. Either way you get one accountable team.',
                'data' => ['alignment' => 'left'],
            ],
            [
                'key' => 'offerings', 'type' => 'features', 'title' => 'How we can help',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Product design sprint', 'description' => 'Two weeks from problem to clickable prototype, validated with users.'],
                        ['icon' => 'layers', 'title' => 'End-to-end product build', 'description' => 'Design and engineering through to a launched, monitored product.'],
                        ['icon' => 'pen-tool', 'title' => 'Brand & identity', 'description' => 'Naming, logo, and a system your whole team can use.'],
                        ['icon' => 'gauge', 'title' => 'Performance & audit', 'description' => 'A hard look at an existing product with a prioritised action list.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Common questions',
                'data' => [
                    'items' => [
                        ['question' => 'How long is a typical engagement?', 'answer' => 'Anywhere from a two-week sprint to a multi-month build. We scope it with you before anything starts.'],
                        ['question' => 'Do you work with existing codebases?', 'answer' => 'Yes — a lot of our work is improving and extending products that already exist.'],
                        ['question' => 'What does it cost?', 'answer' => 'Projects generally start around £15k. Share your budget on the contact form and we will tell you honestly what is realistic.'],
                    ],
                ],
            ],
        ], [
            'meta_title' => 'Services — Lumen Studio',
            'meta_description' => 'Product design sprints, full product builds, brand identity and performance audits.',
            'robots' => 'index, follow',
        ]);

        // Nested child page — exercises the page hierarchy and path building.
        $webDesign = $this->makePage([
            'title' => 'Web Design',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 0,
            'parent_id' => $services->id,
            'excerpt' => 'Marketing sites and web apps that look sharp and load fast.',
            'content' => 'A closer look at our web design and build work.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Web design & build',
                'content' => 'Fast, accessible, and easy to update — sites your team can actually run.',
                'data' => ['alignment' => 'left'],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'What you get',
                'content' => 'A responsive design system, a headless CMS so your team can edit content, and a build that scores green on Core Web Vitals out of the box.',
            ],
        ], [
            'meta_title' => 'Web Design — Lumen Studio',
            'meta_description' => 'Marketing sites and web apps that look sharp and load fast.',
            'robots' => 'index, follow',
        ]);

        $contact = $this->makePage([
            'title' => 'Contact',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 3,
            'excerpt' => 'Tell us about your project.',
            'content' => 'We reply to every enquiry within one business day.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => "Let's talk",
                'content' => 'Fill in the form and we will get back to you within a business day.',
                'data' => ['alignment' => 'center'],
            ],
            [
                'key' => 'details', 'type' => 'contact', 'title' => 'Contact details',
                'data' => [
                    'email' => 'hello@lumenstudio.test',
                    'phone' => '+44 20 7946 0000',
                    'address' => '11 Ashby Mews, London SE4 1TT, UK',
                    'hours' => 'Mon–Fri, 9:00–18:00 GMT',
                ],
            ],
            [
                // Submissions are handled by Web3Forms — no server-side forms.
                'key' => 'contact_form', 'type' => 'contact_form', 'title' => 'Send a message',
                'data' => [
                    'access_key' => '',
                    'subject' => 'New enquiry from the website',
                    'button_label' => 'Send message',
                    'success_message' => "Thanks for reaching out — we'll reply within one business day.",
                    'fields' => [
                        ['name' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'placeholder' => 'Jane Doe'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'jane@company.com'],
                        ['name' => 'company', 'label' => 'Company', 'type' => 'text', 'required' => false, 'placeholder' => 'Acme Inc.'],
                        ['name' => 'message', 'label' => 'What can we help with?', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Tell us about your project…'],
                    ],
                ],
            ],
            [
                'key' => 'map', 'type' => 'map', 'title' => 'Find us',
                'data' => ['lat' => 51.4626, 'lng' => -0.0361, 'zoom' => 14, 'label' => 'Lumen Studio, London'],
            ],
        ], [
            'meta_title' => 'Contact — Lumen Studio',
            'meta_description' => 'Get in touch with Lumen Studio about your project.',
            'robots' => 'index, follow',
        ]);

        // A draft page, so the dashboard shows a non-published status too.
        $careers = $this->makePage([
            'title' => 'Careers',
            'template' => 'default',
            'status' => 'draft',
            'show_in_nav' => false,
            'order' => 4,
            'excerpt' => 'Work with us (coming soon).',
            'content' => 'We are putting this page together — check back soon.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Careers at Lumen',
                'content' => 'We hire slowly and rarely, but when we do we look for senior, curious people.',
                'data' => ['alignment' => 'left'],
            ],
        ], [
            'meta_title' => 'Careers — Lumen Studio',
            'robots' => 'noindex, follow',
        ]);

        return [$home, $about, $services, $webDesign, $contact, $careers];
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<int, array<string, mixed>> $sections
     * @param array<string, mixed> $seo
     */
    private function makePage(array $attributes, array $sections, array $seo): Page
    {
        $attributes['author_id'] = $this->author->id;

        if (($attributes['status'] ?? null) === 'published' && empty($attributes['published_at'])) {
            $attributes['published_at'] = now();
        }

        $page = Page::create($attributes);

        foreach ($sections as $index => $section) {
            PageSection::create([
                'page_id' => $page->id,
                'key' => $section['key'],
                'type' => $section['type'],
                'title' => $section['title'] ?? null,
                'content' => $section['content'] ?? null,
                'data' => $section['data'] ?? null,
                'order' => $index,
                'is_visible' => true,
            ]);
        }

        $page->seo()->create($seo);

        return $page;
    }
}

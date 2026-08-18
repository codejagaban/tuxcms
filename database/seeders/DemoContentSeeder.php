<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Seo;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a self-contained "Demo Site" tenant with realistic sample content:
 * pages (with section-builder sections + SEO), forms, menus and settings.
 *
 * Idempotent — running it again wipes the demo tenant's content and rebuilds
 * it, so it never accumulates duplicates. It only ever touches the Demo Site
 * tenant; any other tenant's data is left alone.
 *
 *   php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    private Tenant $tenant;

    public function run(): void
    {
        $this->resolveTenantAndAdmin();

        // Bind tenant context so BelongsToTenant auto-scopes every write below.
        app()->instance('current_tenant_id', $this->tenant->id);
        app()->instance('current_tenant', $this->tenant);

        $this->wipeExistingDemoContent();
        $this->seedSettings();
        $forms = $this->seedForms();
        $pages = $this->seedPages($forms);
        $this->seedMenus();

        $this->command?->info("Demo content seeded into tenant '{$this->tenant->name}' (id {$this->tenant->id}).");
        $this->command?->info('Pages: ' . count($pages) . ', Forms: ' . count($forms) . '. Log in as admin@tuxcms.com to see it.');
    }

    /**
     * Find (or create) the demo tenant and make sure the super admin belongs
     * to it — the dashboard only lists tenants attached via the pivot table,
     * so without this the seeded content would be invisible after login.
     */
    private function resolveTenantAndAdmin(): void
    {
        $admin = User::where('email', 'admin@tuxcms.com')->first()
            ?? User::factory()->create([
                'name' => 'Super Admin',
                'email' => 'admin@tuxcms.com',
                'is_super_admin' => true,
            ]);

        $this->tenant = Tenant::firstOrCreate(
            ['domain' => 'localhost'],
            [
                'name' => 'Demo Site',
                'owner_id' => $admin->id,
                'plan' => 'pro',
                'is_active' => true,
            ]
        );

        // Attach the super admin as owner (no-op if already attached).
        $this->tenant->users()->syncWithoutDetaching([
            $admin->id => ['role' => 'owner'],
        ]);
    }

    /**
     * Remove any content previously seeded into the demo tenant so the seeder
     * is safe to re-run. Scoped to the demo tenant via the global tenant scope.
     */
    private function wipeExistingDemoContent(): void
    {
        foreach (Page::withTrashed()->get() as $page) {
            Seo::where('seoable_type', Page::class)->where('seoable_id', $page->id)->delete();
            $page->sections()->delete();
            $page->forceDelete();
        }

        foreach (Form::all() as $form) {
            $form->submissions()->delete();
            $form->delete();
        }

        foreach (Menu::all() as $menu) {
            $menu->allItems()->delete();
            $menu->delete();
        }

        Setting::query()->delete();
    }

    private function seedSettings(): void
    {
        $settings = [
            // Site
            ['key' => 'site_name', 'value' => ['Lumen Studio'], 'group' => 'site'],
            ['key' => 'site_tagline', 'value' => ['Design & engineering for growing products'], 'group' => 'site'],
            ['key' => 'site_description', 'value' => ['Lumen Studio is a small product studio that designs and builds web apps, brand systems and marketing sites.'], 'group' => 'site'],
            ['key' => 'site_url', 'value' => ['http://localhost:5173'], 'group' => 'site'],
            ['key' => 'timezone', 'value' => ['Europe/London'], 'group' => 'site'],

            // SEO
            ['key' => 'meta_keywords', 'value' => ['product design, web development, branding, ux'], 'group' => 'seo'],
            ['key' => 'default_og_image', 'value' => ['/images/og-default.jpg'], 'group' => 'seo'],
            ['key' => 'google_analytics_id', 'value' => [''], 'group' => 'seo'],

            // Social
            ['key' => 'social_twitter', 'value' => ['https://twitter.com/lumenstudio'], 'group' => 'social'],
            ['key' => 'social_linkedin', 'value' => ['https://www.linkedin.com/company/lumenstudio'], 'group' => 'social'],
            ['key' => 'social_github', 'value' => ['https://github.com/lumenstudio'], 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => ['https://instagram.com/lumen.studio'], 'group' => 'social'],

            // Email
            ['key' => 'contact_email', 'value' => ['hello@lumenstudio.test'], 'group' => 'email'],
            ['key' => 'notification_email', 'value' => ['team@lumenstudio.test'], 'group' => 'email'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }

    /**
     * @return array<string, Form>
     */
    private function seedForms(): array
    {
        $contact = Form::create([
            'name' => 'Contact Us',
            'notification_email' => 'team@lumenstudio.test',
            'success_message' => "Thanks for reaching out — we'll reply within one business day.",
            'is_active' => true,
            'fields' => [
                ['name' => 'full_name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'placeholder' => 'Jane Doe', 'validation' => 'string|max:255'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'jane@company.com', 'validation' => 'email|max:255'],
                ['name' => 'company', 'label' => 'Company', 'type' => 'text', 'required' => false, 'placeholder' => 'Acme Inc.', 'validation' => 'string|max:255'],
                ['name' => 'budget', 'label' => 'Estimated budget', 'type' => 'select', 'required' => false, 'options' => ['< £5k', '£5k–£15k', '£15k–£50k', '£50k+'], 'validation' => 'string|max:50'],
                ['name' => 'message', 'label' => 'What can we help with?', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Tell us about your project…', 'validation' => 'string|max:5000'],
            ],
        ]);

        $newsletter = Form::create([
            'name' => 'Newsletter Signup',
            'notification_email' => 'team@lumenstudio.test',
            'success_message' => "You're on the list. Watch your inbox for the next issue.",
            'is_active' => true,
            'fields' => [
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'you@email.com', 'validation' => 'email|max:255'],
            ],
        ]);

        return ['contact' => $contact, 'newsletter' => $newsletter];
    }

    /**
     * @param array<string, Form> $forms
     * @return array<int, Page>
     */
    private function seedPages(array $forms): array
    {
        $home = $this->makePage([
            'title' => 'Home',
            'template' => 'home',
            'status' => 'published',
            'is_homepage' => true,
            'order' => 0,
            'excerpt' => 'Lumen Studio designs and builds digital products people actually enjoy using.',
            'content' => 'Lumen Studio is a compact product team combining design, engineering and strategy.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'We design & build products that earn their keep',
                'content' => 'From first sketch to production, Lumen Studio ships polished web apps, brand systems and marketing sites for teams that care about the details.',
                'data' => [
                    'subheading' => 'A product studio for founders and growing teams',
                    'primary_cta' => ['label' => 'Start a project', 'url' => '/contact'],
                    'secondary_cta' => ['label' => 'See our work', 'url' => '/services'],
                    'background_image' => '/images/hero-home.jpg',
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
                'data' => ['primary_cta' => ['label' => 'Get in touch', 'url' => '/contact']],
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
            'order' => 1,
            'excerpt' => 'A small, senior team that treats your product like our own.',
            'content' => 'We started Lumen in 2014 with a simple idea: keep the team small, keep the quality high.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Small team, senior work',
                'content' => 'No account managers, no handoffs to juniors. You work directly with the people doing the work.',
                'data' => ['alignment' => 'left', 'background_image' => '/images/hero-about.jpg'],
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

        // Nested child page under Services to exercise the page hierarchy.
        $webDesign = $this->makePage([
            'title' => 'Web Design',
            'template' => 'default',
            'status' => 'published',
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
            'template' => 'contact',
            'status' => 'published',
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
                'key' => 'contact_form', 'type' => 'form', 'title' => 'Send a message',
                'data' => [
                    'form_slug' => $forms['contact']->slug,
                    'form_id' => $forms['contact']->id,
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
     * Create a page with its ordered sections and SEO record.
     *
     * @param array<string, mixed> $attributes
     * @param array<int, array<string, mixed>> $sections
     * @param array<string, mixed> $seo
     */
    private function makePage(array $attributes, array $sections, array $seo): Page
    {
        $attributes['author_id'] = $this->tenant->owner_id;

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

    private function seedMenus(): void
    {
        $header = Menu::create(['name' => 'Header', 'slug' => 'header', 'location' => 'header']);
        $this->addItems($header, [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'About', 'url' => '/about'],
            ['title' => 'Services', 'url' => '/services'],
            ['title' => 'Contact', 'url' => '/contact'],
        ]);

        $footer = Menu::create(['name' => 'Footer', 'slug' => 'footer', 'location' => 'footer']);
        $this->addItems($footer, [
            ['title' => 'Services', 'url' => '/services'],
            ['title' => 'About', 'url' => '/about'],
            ['title' => 'Careers', 'url' => '/careers'],
            ['title' => 'Contact', 'url' => '/contact'],
        ]);
    }

    /**
     * @param array<int, array{title: string, url: string}> $items
     */
    private function addItems(Menu $menu, array $items): void
    {
        foreach ($items as $index => $item) {
            MenuItem::create([
                'menu_id' => $menu->id,
                'title' => $item['title'],
                'url' => $item['url'],
                'target' => '_self',
                'type' => 'custom',
                'order' => $index,
            ]);
        }
    }
}

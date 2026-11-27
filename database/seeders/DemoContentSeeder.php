<?php

namespace Database\Seeders;

use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\MediaLibrary;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Seo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds a realistic business website that uses everything the CMS does:
 * pages built from every section type, photographs in the media library,
 * nested service pages, a draft and a scheduled page, SEO, site settings,
 * vacancies in each status, and an inbox
 * of enquiries and applications, including ones the spam filter is holding.
 *
 * Idempotent — re-running wipes the demo pages and rebuilds them, so it never
 * accumulates duplicates. It replaces every page in the database, so it is
 * for an empty or throwaway database only.
 *
 *   php artisan db:seed --class=DemoContentSeeder
 */
class DemoContentSeeder extends Seeder
{
    private User $author;

    /** @var array<string, string> photograph name => its address in the media library */
    private array $photos = [];

    public function run(): void
    {
        $this->author = User::firstOrCreate(
            ['email' => 'admin@tuxcms.com'],
            ['name' => 'Site Admin', 'password' => Hash::make('password')]
        );

        $this->wipeExistingPages();
        $this->seedPhotographs();
        $pages = $this->seedPages();
        $this->seedSettings();
        $jobs = $this->seedJobs();
        $this->seedInbox($jobs);

        $this->command?->info('Demo content seeded: ' . count($pages) . ' pages, ' . count($jobs) . ' vacancies and a sample inbox.');
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
                    'background_image' => $this->photos['desk-from-above'],
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
                // Features with photographs: the theme hangs them as a wall of work.
                'key' => 'work', 'type' => 'features', 'title' => 'Selected work',
                'content' => 'A few of the products and brands we have helped into the world.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Fathom bookings', 'description' => 'A booking flow rebuilt around how people actually plan a trip.', 'image' => $this->photos['laptop-and-notebook'], 'url' => '/services/web-design/', 'link_label' => 'How we build'],
                        ['icon' => 'pen-tool', 'title' => 'Overlap brand book', 'description' => 'Guidelines short enough that the whole company read them.', 'image' => $this->photos['printed-pages'], 'url' => '/services/brand-identity/', 'link_label' => 'How we brand'],
                        ['icon' => 'sparkles', 'title' => 'Symbol identity', 'description' => 'A name, a mark and a launch in eight weeks.', 'image' => $this->photos['identity-on-a-desk'], 'url' => '', 'link_label' => ''],
                        ['icon' => 'layers', 'title' => 'Field kit', 'description' => 'One design system across an app, a site and a shelf of hardware.', 'image' => $this->photos['kit-laid-out'], 'url' => '', 'link_label' => ''],
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
                'data' => ['alignment' => 'left', 'background_image' => '', 'image' => $this->photos['room-by-the-windows'], 'image_alt' => 'People working at long tables beside tall windows'],
            ],
            [
                'key' => 'story', 'type' => 'text', 'title' => 'Our story',
                'data' => ['image' => $this->photos['studio-table'], 'image_alt' => 'A long shared table in the studio'],
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

        // A second service page, so the Services menu has more than one entry.
        $brand = $this->makePage([
            'title' => 'Brand Identity',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $services->id,
            'show_in_nav' => true,
            'order' => 1,
            'excerpt' => 'Naming, marks and a system the whole team can use.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Brand identity',
                'content' => 'A name, a mark and the rules that keep them consistent everywhere they appear.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Talk to us', 'url' => '/contact/']],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'How it runs',
                'content' => "We start with a week of listening: to you, your customers and the people who answer your phones.\n\nThen three directions, one chosen, and a guide short enough that people read it.",
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'Starting from a blank page?',
                'content' => 'Send us what you have, even if it is only a name.',
                'data' => ['primary_cta' => ['label' => 'Get in touch', 'url' => '/contact/']],
            ],
        ], [
            'meta_title' => 'Brand identity — Lumen Studio',
            'meta_description' => 'Naming, logo design and brand guidelines from Lumen Studio.',
            'robots' => 'index, follow',
        ]);

        // Kept out of the menu; the footer links to it through the legal settings.
        $privacy = $this->makePage([
            'title' => 'Privacy',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => false,
            'order' => 8,
            'excerpt' => 'How we use the details you send us.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Privacy',
                'data' => ['alignment' => 'left'],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'What we keep',
                'content' => "When you write to us or apply for a role, we keep what you send so that we can reply.\n\nWe do not sell it or pass it on, and you can ask us to delete it at any time.",
            ],
        ], [
            'meta_title' => 'Privacy — Lumen Studio',
            'robots' => 'noindex, follow',
        ]);

        // Published, but dated in the future: it goes live by itself on the
        // day, once the scheduler next rebuilds the site.
        $openDay = $this->makePage([
            'title' => 'Open Studio Day',
            'template' => 'default',
            'status' => 'published',
            'published_at' => now()->addDays(14),
            'show_in_nav' => false,
            'order' => 9,
            'excerpt' => 'Come and see how we work.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Open studio day',
                'content' => 'Coffee, work in progress on the walls, and the whole team in one room.',
                'data' => ['subheading' => 'Doors open at ten'],
            ],
        ], [
            'meta_title' => 'Open studio day — Lumen Studio',
            'robots' => 'index, follow',
        ]);

        // A draft page, so the dashboard shows a non-published status too.
        $caseStudies = $this->makePage([
            'title' => 'Case Studies',
            'template' => 'default',
            'status' => 'draft',
            'show_in_nav' => false,
            'order' => 4,
            'excerpt' => 'Work we are proud of (coming soon).',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Case studies',
                'content' => 'We are writing these up. Check back soon.',
                'data' => ['alignment' => 'left'],
            ],
        ], [
            'meta_title' => 'Case studies — Lumen Studio',
            'robots' => 'noindex, follow',
        ]);

        return [$home, $about, $services, $webDesign, $brand, $contact, $privacy, $openDay, $caseStudies];
    }

    /**
     * Put the demo photographs in the media library, where the Media screen
     * lists them and an editor can pick them for any section.
     */
    private function seedPhotographs(): void
    {
        $library = MediaLibrary::singleton();
        $captions = [
            'desk-from-above' => 'A desk seen from above',
            'laptop-and-notebook' => 'A laptop and an open notebook',
            'printed-pages' => 'Printed pages and magazines laid out',
            'identity-on-a-desk' => 'A laptop and a book on a white desk',
            'kit-laid-out' => 'Tools and devices laid out in rows',
            'studio-table' => 'A long shared table in the studio',
            'room-by-the-windows' => 'People working beside tall windows',
        ];

        foreach ($captions as $name => $alt) {
            $media = $library->getMedia('library')->firstWhere('file_name', $name . '.jpg')
                ?? $library->addMedia(database_path("seeders/demo-media/{$name}.jpg"))
                    ->preservingOriginal()
                    ->usingName($alt)
                    ->withCustomProperties(['alt_text' => $alt])
                    ->toMediaCollection('library');

            // Root-relative, so the pages do not carry this machine's address.
            $this->photos[$name] = parse_url($media->getUrl(), PHP_URL_PATH);
            $library->unsetRelation('media');
        }
    }

    /** The site-wide details the header, footer, forms and schema draw on. */
    private function seedSettings(): void
    {
        $settings = [
            'site' => [
                'site_name' => 'Lumen Studio',
                'site_tagline' => 'Design and engineering for products that have to earn their keep.',
                'site_description' => 'Lumen Studio designs and builds web apps, brand systems and marketing sites.',
            ],
            'contact' => [
                'contact_email' => 'hello@lumenstudio.test',
                'contact_phone' => '+44 20 7946 0000',
                'contact_address' => '11 Ashby Mews, London SE4 1TT',
            ],
            'legal' => ['privacy_url' => '/privacy/'],
        ];

        foreach ($settings as $group => $values) {
            foreach ($values as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }
    }

    /**
     * One vacancy in each state: two open (one with a closing date), a draft
     * and a closed one. Only the open ones are built into the careers pages.
     *
     * @return array<int, JobPost>
     */
    private function seedJobs(): array
    {
        $jobs = [
            [
                'slug' => 'senior-product-designer', 'title' => 'Senior product designer', 'status' => 'published',
                'published_at' => now()->subDays(6), 'closes_at' => now()->addDays(24),
                'location' => 'London', 'job_type' => 'Full-time', 'employment_type' => 'full-time',
                'hours' => '37.5 a week', 'salary' => '£62,000 to £70,000',
                'summary' => 'Lead design on two client products, from first research to launch.',
                'description' => "You will own the design of two products at a time and sit beside the engineers building them.\n\nMost weeks include time with the people who use what we make.",
                'responsibilities' => ['Run discovery with clients and their customers', 'Design, prototype and test flows', 'Keep the design system honest as products grow'],
                'essential' => ['Five or more years designing shipped products', 'A portfolio that shows your thinking, not only the result'],
                'desirable' => ['Comfort reading front-end code'],
                'benefits' => ['Four-day week in August', 'A real training budget', 'Pension matched to 6%'],
            ],
            [
                'slug' => 'full-stack-engineer', 'title' => 'Full-stack engineer', 'status' => 'published',
                'published_at' => now()->subDays(2),
                'location' => 'London or remote in the UK', 'job_type' => 'Full-time', 'employment_type' => 'full-time',
                'hours' => '37.5 a week', 'salary' => '£58,000 to £68,000',
                'summary' => 'Build and look after client products across the whole stack.',
                'description' => 'You will join a pod of three and take features from a sketch to production.',
                'responsibilities' => ['Ship features end to end', 'Review code with care', 'Keep products fast and observable'],
                'essential' => ['Strong experience with a modern web stack', 'You write tests because you want to'],
                'benefits' => ['Four-day week in August', 'Pension matched to 6%'],
            ],
            [
                'slug' => 'studio-manager', 'title' => 'Studio manager', 'status' => 'draft',
                'location' => 'London', 'job_type' => 'Part-time', 'employment_type' => 'part-time',
                'summary' => 'Keep the studio, its diary and its suppliers running smoothly.',
            ],
            [
                'slug' => 'design-intern', 'title' => 'Design intern', 'status' => 'closed',
                'published_at' => now()->subMonths(3), 'closes_at' => now()->subMonths(2),
                'location' => 'London', 'job_type' => 'Internship', 'employment_type' => 'intern',
                'summary' => 'A paid twelve-week placement with the design team.',
            ],
        ];

        return array_map(fn (array $job) => JobPost::updateOrCreate(
            ['slug' => $job['slug']],
            [...$job, 'author_id' => $this->author->id]
        ), $jobs);
    }

    /**
     * A believable inbox: delivered messages, one whose email failed, and one
     * of each kind that the spam filter is holding with its reason, so the
     * release button in the dashboard has something to act on.
     *
     * @param array<int, JobPost> $jobs
     */
    private function seedInbox(array $jobs): void
    {
        $enquiries = [
            ['email' => 'hana.suzuki@fathom.test', 'type' => 'contact', 'name' => 'Hana Suzuki', 'organisation' => 'Fathom',
                'phone' => '07700 900118', 'message' => 'We are rebuilding our booking flow this spring and would like to talk about a design sprint.',
                'delivery_status' => 'sent', 'emailed_at' => now()->subHours(5)],
            ['email' => 'joe@overlap.test', 'type' => 'contact', 'name' => 'Joe Adeyemi', 'organisation' => 'Overlap',
                'message' => 'Could you audit our onboarding? Sign-ups drop sharply at the second step.',
                'details' => ['budget' => '£15k to £25k', 'timeline' => 'Next quarter'],
                'delivery_status' => 'sent', 'emailed_at' => now()->subDays(2)],
            ['email' => 'mira@harbourline.test', 'type' => 'contact', 'name' => 'Mira Castellanos',
                'message' => 'Do you take on small brand projects? We are a two-person bakery.',
                'delivery_status' => 'failed'],
            ['email' => 'growth@rank-rocket.test', 'type' => 'contact', 'name' => 'SEO Growth Team',
                'message' => 'We can get your website to the first page of Google. Reply for our price list and a free audit of your site.',
                'delivery_status' => 'spam', 'spam_reason' => 'Reads as a sales pitch'],
        ];

        foreach ($enquiries as $enquiry) {
            Enquiry::updateOrCreate(['email' => $enquiry['email']], $enquiry);
        }

        $designer = $jobs[0];
        $engineer = $jobs[1];
        $applications = [
            ['email' => 'ada.okonkwo@mail.test', 'job_post_id' => $designer->id, 'name' => 'Ada Okonkwo', 'phone' => '07700 900245',
                'cover_letter' => 'I have led design on three shipped products and would love to do the same with your clients.',
                'delivery_status' => 'sent', 'emailed_at' => now()->subDay()],
            ['email' => 'tomas.lind@mail.test', 'job_post_id' => $engineer->id, 'name' => 'Tomas Lind',
                'cover_letter' => 'Seven years across Laravel and React, most recently on a product with two million users.',
                'delivery_status' => 'sent', 'emailed_at' => now()->subHours(9)],
            ['email' => 'apply-now@bulk-cv.test', 'job_post_id' => $engineer->id, 'name' => 'Recruitment Partner',
                'cover_letter' => 'We supply pre-vetted developers at competitive rates. Contact us for our full candidate list.',
                'delivery_status' => 'spam', 'spam_reason' => 'Sent without opening the form page'],
        ];

        foreach ($applications as $application) {
            // A stand-in CV, so the download button in the dashboard works.
            $path = 'job-applications/demo/' . md5($application['email']) . '.pdf';
            Storage::disk('local')->put($path, "%PDF-1.4\n% Demo CV for {$application['name']}\n%%EOF\n");

            JobApplication::updateOrCreate(
                ['email' => $application['email']],
                [...$application, 'cv_path' => $path, 'cv_name' => str_replace(' ', '-', strtolower($application['name'])) . '-cv.pdf']
            );
        }
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

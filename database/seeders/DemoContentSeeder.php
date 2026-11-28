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
 * Seeds TuxCMS's own product website, which uses everything the CMS does:
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
            'excerpt' => 'TuxCMS lets a business edit its website on the page itself, then publishes it as fast static HTML.',
            'content' => 'TuxCMS is a content management system for one business website.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Edit your website where it lives',
                'content' => 'Click any words on your real pages and change them. When you are happy, TuxCMS publishes the whole site as plain HTML that loads before a visitor can blink.',
                'data' => [
                    'subheading' => 'The CMS for one business website',
                    'primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/'],
                    'secondary_cta' => ['label' => 'See how editing works', 'url' => '/features/editing/'],
                    'background_image' => '',
                    'alignment' => 'center',
                ],
            ],
            [
                'key' => 'features', 'type' => 'features', 'title' => 'Everything one site needs',
                'content' => 'Pages, careers, an inbox and a media library, in one dashboard your whole team can use.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'layers', 'title' => 'Pages from sections', 'description' => 'Build a page from eleven section types and reorder them by dragging. Nest pages under each other and the menu follows.', 'url' => '/features/editing/', 'link_label' => 'Editing'],
                        ['icon' => 'zap', 'title' => 'Static, so it is fast', 'description' => 'Publishing writes every page out as HTML. Visitors never wait on a database, and there is nothing on the server to break into.', 'url' => '/features/publishing/', 'link_label' => 'Publishing'],
                        ['icon' => 'shield', 'title' => 'An inbox that filters spam', 'description' => 'Contact forms and job applications land in the dashboard. Anything that looks like spam is held back for you to check.'],
                    ],
                ],
            ],
            [
                // Features with photographs: the theme hangs them as a wall.
                'key' => 'work', 'type' => 'features', 'title' => 'What you can run on it',
                'content' => 'Every part of a small business website, edited from the same place.',
                'data' => [
                    // One column: the theme lays these out as a showcase.
                    'columns' => 1,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Services, page by page', 'description' => 'A page for each thing you sell, grouped under one heading in the menu.', 'url' => '/features/', 'link_label' => 'All features'],
                        ['icon' => 'briefcase', 'title' => 'A careers page', 'description' => 'Post a role, set a closing date and read applications with their CVs attached.', 'url' => '/careers/', 'link_label' => 'Open roles'],
                        ['icon' => 'image', 'title' => 'A media library', 'description' => 'Upload a photograph once and use it on any page, with its alt text kept beside it.'],
                        ['icon' => 'search', 'title' => 'Search that works', 'description' => 'Titles, descriptions and structured data are written for every page as it is published.'],
                    ],
                ],
            ],
            [
                'key' => 'stats', 'type' => 'stats', 'title' => 'Built to stay out of the way',
                'data' => [
                    'items' => [
                        ['label' => 'Section types to build pages from', 'value' => '11'],
                        ['label' => 'Database queries when someone visits', 'value' => '0'],
                        ['label' => 'Days held spam is kept for you to check', 'value' => '30'],
                        ['label' => 'Dashboard for the whole site', 'value' => '1'],
                    ],
                ],
            ],
            [
                'key' => 'testimonials', 'type' => 'testimonials', 'title' => 'From the people who edit',
                'data' => [
                    'items' => [
                        ['quote' => 'I change the opening hours myself now. I click them, type, and press Publish. That used to be an email and a week.', 'author' => 'Practice manager', 'role' => 'A dental clinic, sample quote'],
                        ['quote' => 'Applications arrive with the CV attached and the spam set aside. I stopped dreading the careers inbox.', 'author' => 'Office lead', 'role' => 'An architecture studio, sample quote'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'Try it on these pages',
                'content' => 'The demo dashboard edits this very site. Sign in, change a headline and publish.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'TuxCMS: edit your website where it lives',
            'meta_description' => 'A CMS for one business website. Edit on the page itself, publish as fast static HTML.',
            'meta_keywords' => 'cms, static site, website editor, small business website',
            'og_title' => 'TuxCMS',
            'og_description' => 'Edit your website where it lives.',
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
            'excerpt' => 'Why TuxCMS exists, and who makes it.',
            'content' => 'TuxCMS is made by a small team who got tired of websites nobody dared to edit.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'For websites people dare to edit',
                'content' => 'Most small business sites go stale because changing them feels risky. We made one where the page you edit is the page you get.',
                'data' => ['alignment' => 'left', 'background_image' => '', 'image' => $this->photos['room-by-the-windows'], 'image_alt' => 'People working at long tables beside tall windows'],
            ],
            [
                'key' => 'story', 'type' => 'text', 'title' => 'Why we built it',
                'data' => ['image' => $this->photos['studio-table'], 'image_alt' => 'A long shared table in the office'],
                'content' => "We spent years building websites for clinics, studios and shops. They all asked for the same thing: to change a price or a photograph without calling anyone.\n\nSo TuxCMS puts the editing on the page itself, and keeps the published site as plain files that are fast, cheap to host and hard to break.",
            ],
            [
                'key' => 'team', 'type' => 'team', 'title' => 'The people',
                'data' => [
                    'members' => [
                        ['name' => 'Ada Okafor', 'role' => 'Product and design', 'bio' => 'Decides what goes in the dashboard, and mostly what stays out.'],
                        ['name' => 'Tom Reyes', 'role' => 'Engineering', 'bio' => 'Keeps publishing fast. Cares far too much about load times.'],
                        ['name' => 'Sofia Bianchi', 'role' => 'Support', 'bio' => 'Answers every question herself, and turns the common ones into features.'],
                    ],
                ],
            ],
        ], [
            'meta_title' => 'About TuxCMS',
            'meta_description' => 'Why TuxCMS exists, and the small team that makes it.',
            'og_type' => 'website',
            'robots' => 'index, follow',
        ]);

        $features = $this->makePage([
            'title' => 'Features',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 2,
            'excerpt' => 'Editing, publishing, careers, enquiries and media in one dashboard.',
            'content' => 'Everything TuxCMS does for a business website.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Features',
                'content' => 'One dashboard for the pages, the people applying to work with you and the messages your customers send.',
                'data' => ['alignment' => 'left'],
            ],
            [
                'key' => 'offerings', 'type' => 'features', 'title' => 'What is in the dashboard',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Live preview editing', 'description' => 'Every editable part of the real page is outlined. Click it and type.'],
                        ['icon' => 'layers', 'title' => 'Revisions', 'description' => 'Each save keeps a copy, so any page can go back to how it was.'],
                        ['icon' => 'pen-tool', 'title' => 'Careers and applications', 'description' => 'Vacancies with closing dates, and applications with their CVs.'],
                        ['icon' => 'gauge', 'title' => 'Scheduled publishing', 'description' => 'Date a page for next week and it goes live on the day by itself.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Common questions',
                'data' => [
                    'items' => [
                        ['question' => 'Do I need a developer to change my site?', 'answer' => 'No. Words, photographs, sections and pages are all edited in the dashboard. A developer is only needed to make a new theme.'],
                        ['question' => 'Where does the site run?', 'answer' => 'Anywhere that serves files. The published site is plain HTML, CSS and images, so ordinary shared hosting is plenty.'],
                        ['question' => 'What happens to spam?', 'answer' => 'It is held in the inbox, marked with the reason, and never emailed on. You can release anything genuine with one click.'],
                    ],
                ],
            ],
        ], [
            'meta_title' => 'Features | TuxCMS',
            'meta_description' => 'Live preview editing, revisions, careers, an inbox with a spam filter, and scheduled publishing.',
            'robots' => 'index, follow',
        ]);

        // Nested child page: exercises the page hierarchy and path building.
        $editing = $this->makePage([
            'title' => 'Editing',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 0,
            'parent_id' => $features->id,
            'excerpt' => 'Change the words on the page you are looking at.',
            'content' => 'How editing works in TuxCMS.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Editing on the page',
                'content' => 'The dashboard shows your real page, with everything you can change outlined in blue.',
                'data' => ['alignment' => 'left'],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'How it feels',
                'content' => "Open a page and you see it exactly as visitors will. Click a heading and type over it. Drag a section to move it, or add a new one from the list.\n\nNothing goes live until you press Publish, and every save is kept as a revision.",
            ],
        ], [
            'meta_title' => 'Editing | TuxCMS',
            'meta_description' => 'Edit your website on the page itself, with every change kept as a revision.',
            'robots' => 'index, follow',
        ]);

        $contact = $this->makePage([
            'title' => 'Contact',
            'template' => 'default',
            'status' => 'published',
            'show_in_nav' => true,
            'order' => 3,
            'excerpt' => 'Ask us anything about TuxCMS.',
            'content' => 'We reply to every message within one working day.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Talk to us',
                'content' => 'Questions about moving your site over, hosting or anything else. A person reads every message.',
                'data' => ['alignment' => 'left'],
            ],
            [
                'key' => 'details', 'type' => 'contact', 'title' => 'Contact details',
                'data' => [
                    'email' => 'hello@tuxcms.test',
                    'phone' => '+44 20 7946 0000',
                    'address' => '11 Ashby Mews, London SE4 1TT, UK',
                    'hours' => 'Mon–Fri, 9:00–18:00 GMT',
                ],
            ],
            [
                // With no Web3Forms key, this goes to the dashboard's own inbox.
                'key' => 'contact_form', 'type' => 'contact_form', 'title' => 'Send a message',
                'data' => [
                    'access_key' => '',
                    'subject' => 'New enquiry from the website',
                    'button_label' => 'Send message',
                    'success_message' => 'Thanks. Your message is in our inbox and we will reply within one working day.',
                    'fields' => [
                        ['name' => 'name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'placeholder' => 'Jane Doe'],
                        ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'placeholder' => 'jane@company.com'],
                        ['name' => 'company', 'label' => 'Business', 'type' => 'text', 'required' => false, 'placeholder' => 'Acme Dental'],
                        ['name' => 'message', 'label' => 'What can we help with?', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Tell us about your website'],
                    ],
                ],
            ],
            [
                'key' => 'map', 'type' => 'map', 'title' => 'Find us',
                'data' => ['lat' => 51.4626, 'lng' => -0.0361, 'zoom' => 14, 'label' => 'TuxCMS, London'],
            ],
        ], [
            'meta_title' => 'Contact | TuxCMS',
            'meta_description' => 'Get in touch with the TuxCMS team.',
            'robots' => 'index, follow',
        ]);

        // A second feature page, so the Features menu has more than one entry.
        $publishing = $this->makePage([
            'title' => 'Publishing',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 1,
            'excerpt' => 'One button writes the whole site out as fast static files.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'One button to publish',
                'content' => 'Publish writes every page, the careers pages and the sitemap out as plain files in under a second.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Talk to us', 'url' => '/contact/']],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'Why static',
                'content' => "A static page has nothing to run when someone opens it, so it is fast everywhere and costs almost nothing to host.\n\nThere is no login page on the public site and no database for anyone to reach.",
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it publish',
                'content' => 'Sign in to the demo, change something and press Publish.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Publishing | TuxCMS',
            'meta_description' => 'TuxCMS publishes the whole site as fast static HTML with one button.',
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
            'meta_title' => 'Privacy | TuxCMS',
            'robots' => 'noindex, follow',
        ]);

        // Published, but dated in the future: it goes live by itself on the
        // day, once the scheduler next rebuilds the site.
        $release = $this->makePage([
            'title' => 'TuxCMS 2.0',
            'template' => 'default',
            'status' => 'published',
            'published_at' => now()->addDays(14),
            'show_in_nav' => false,
            'order' => 9,
            'excerpt' => 'The next release, and what is in it.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'TuxCMS 2.0',
                'content' => 'Shared drafts, comments on any section and a faster publish.',
                'data' => ['subheading' => 'Out in two weeks'],
            ],
        ], [
            'meta_title' => 'TuxCMS 2.0',
            'robots' => 'index, follow',
        ]);

        // A draft page, so the dashboard shows a non-published status too.
        $stories = $this->makePage([
            'title' => 'Customer Stories',
            'template' => 'default',
            'status' => 'draft',
            'show_in_nav' => false,
            'order' => 4,
            'excerpt' => 'Businesses running their sites on TuxCMS (coming soon).',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Customer stories',
                'content' => 'We are writing these up. Check back soon.',
                'data' => ['alignment' => 'left'],
            ],
        ], [
            'meta_title' => 'Customer stories | TuxCMS',
            'robots' => 'noindex, follow',
        ]);

        return [$home, $about, $features, $editing, $publishing, $contact, $privacy, $release, $stories];
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
                'site_name' => 'TuxCMS',
                'site_tagline' => 'Edit your website where it lives. Publish it as fast static HTML.',
                'site_description' => 'TuxCMS is a content management system for one business website.',
            ],
            'contact' => [
                'contact_email' => 'hello@tuxcms.test',
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
                'summary' => 'Lead design on the dashboard and the themes that ship with it.',
                'description' => "You will own how editing feels in TuxCMS, from the live preview to the inbox, and sit beside the engineers building it.\n\nMost weeks include time with the people who edit their sites with it.",
                'responsibilities' => ['Talk to the people who edit their sites every week', 'Design, prototype and test new dashboard flows', 'Keep the dashboard and the themes one system'],
                'essential' => ['Five or more years designing shipped products', 'A portfolio that shows your thinking, not only the result'],
                'desirable' => ['Comfort reading front-end code'],
                'benefits' => ['Four-day week in August', 'A real training budget', 'Pension matched to 6%'],
            ],
            [
                'slug' => 'full-stack-engineer', 'title' => 'Full-stack engineer', 'status' => 'published',
                'published_at' => now()->subDays(2),
                'location' => 'London or remote in the UK', 'job_type' => 'Full-time', 'employment_type' => 'full-time',
                'hours' => '37.5 a week', 'salary' => '£58,000 to £68,000',
                'summary' => 'Build TuxCMS across Laravel, React and the static site builder.',
                'description' => 'You will take features from a sketch to a release, across the API, the dashboard and publishing.',
                'responsibilities' => ['Ship features end to end', 'Review code with care', 'Keep publishing fast'],
                'essential' => ['Strong experience with a modern web stack', 'You write tests because you want to'],
                'benefits' => ['Four-day week in August', 'Pension matched to 6%'],
            ],
            [
                'slug' => 'support-lead', 'title' => 'Support lead', 'status' => 'draft',
                'location' => 'London', 'job_type' => 'Part-time', 'employment_type' => 'part-time',
                'summary' => 'Answer customers, write the help pages and tell us what to fix next.',
            ],
            [
                'slug' => 'design-intern', 'title' => 'Design intern', 'status' => 'closed',
                'published_at' => now()->subMonths(3), 'closes_at' => now()->subMonths(2),
                'location' => 'London', 'job_type' => 'Internship', 'employment_type' => 'intern',
                'summary' => 'A paid twelve-week placement working on the themes.',
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
                'phone' => '07700 900118', 'message' => 'We run three dental clinics and want our receptionists to update opening hours themselves. Can we move our current site over?',
                'delivery_status' => 'sent', 'emailed_at' => now()->subHours(5)],
            ['email' => 'joe@overlap.test', 'type' => 'contact', 'name' => 'Joe Adeyemi', 'organisation' => 'Overlap',
                'message' => 'Does TuxCMS work on ordinary shared hosting? Our current host only gives us PHP and a file manager.',
                'details' => ['pages' => 'About 20', 'timeline' => 'Next quarter'],
                'delivery_status' => 'sent', 'emailed_at' => now()->subDays(2)],
            ['email' => 'mira@harbourline.test', 'type' => 'contact', 'name' => 'Mira Castellanos',
                'message' => 'Can a theme be made to match the sign over our bakery? We are only two people.',
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
                'cover_letter' => 'I have led design on two content tools and would love to make editing a website feel this direct.',
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

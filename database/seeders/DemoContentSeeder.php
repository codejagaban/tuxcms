<?php

namespace Database\Seeders;

use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\MediaLibrary;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Post;
use App\Models\Seo;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds TuxCMS's own product website, which uses everything the CMS does:
 * pages built from every section type, photographs in the media library,
 * nested service pages, a draft and a scheduled page, SEO, site settings,
 * vacancies in each status, a blog with published, scheduled and draft
 * posts, and an inbox of enquiries and applications, including ones the spam
 * filter is holding.
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
        $posts = $this->seedPosts();

        $this->command?->info('Demo content seeded: ' . count($pages) . ' pages, ' . count($jobs) . ' vacancies, ' . count($posts) . ' blog posts and a sample inbox.');
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
                // The template's opening "team behind it" block: a tall photograph,
                // a heading and the words beside it.
                'key' => 'story', 'type' => 'text', 'title' => 'Made by site builders',
                'data' => ['image' => $this->photos['laptop-and-notebook'], 'image_alt' => 'A laptop and an open notebook'],
                'content' => "For years we built websites for clinics, studios and shops, and every one of them went stale because nobody dared to touch it.\n\nTuxCMS is what we wished we could have handed over: the page you edit is the page you get, and the published site is plain files that are fast and hard to break.",
            ],
            [
                'key' => 'features', 'type' => 'features', 'title' => 'Everything one site needs',
                'content' => 'Pages, careers, an inbox and a media library, in one dashboard your whole team can use.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'layers', 'title' => 'Pages from sections', 'description' => 'Build a page from eleven section types and reorder them by dragging. Nest pages under each other and the menu follows.', 'url' => '/features/themes-and-sections/', 'link_label' => 'Themes and sections'],
                        ['icon' => 'zap', 'title' => 'Static, so it is fast', 'description' => 'Publishing writes every page out as HTML. Visitors never wait on a database, and there is nothing on the server to break into.', 'url' => '/features/publishing/', 'link_label' => 'Publishing'],
                        ['icon' => 'shield', 'title' => 'An inbox that filters spam', 'description' => 'Contact forms and job applications land in the dashboard. Anything that looks like spam is held back for you to check.', 'url' => '/features/inbox/', 'link_label' => 'Inbox'],
                    ],
                ],
            ],
            [
                // Features with photographs: the theme hangs them as a wall.
                'key' => 'work', 'type' => 'features', 'title' => 'What you can run on it',
                'content' => 'Every part of a small business website, edited from the same place.',
                'data' => [
                    // One column: the theme lays these out as its key-features grid.
                    'columns' => 1,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Services, page by page', 'description' => 'A page for each thing you sell, grouped under one heading in the menu.', 'url' => '/features/', 'link_label' => 'All features'],
                        ['icon' => 'briefcase', 'title' => 'A careers page', 'description' => 'Post a role, set a closing date and read applications with their CVs attached.', 'url' => '/features/careers/', 'link_label' => 'Careers'],
                        ['icon' => 'image', 'title' => 'A media library', 'description' => 'Upload a photograph once and use it on any page, with its alt text kept beside it.', 'url' => '/features/media-library/', 'link_label' => 'Media library'],
                        ['icon' => 'search', 'title' => 'Search that works', 'description' => 'Titles, descriptions and structured data are written for every page as it is published.', 'url' => '/features/search-and-seo/', 'link_label' => 'Search and SEO'],
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
                // The theme's own section types, declared in its theme.json.
                'key' => 'pricing', 'type' => 'pricing', 'title' => 'Simple pricing for one site',
                'content' => 'Sample plans for this demo. Every plan is the whole CMS; you pay for how much help you want with it.',
                'data' => [
                    'promises' => ['Free 30-day trial', 'Cancel anytime'],
                    'plans' => [
                        ['name' => 'Self-hosted', 'summary' => 'For teams with a developer', 'price' => 'Free', 'period' => '', 'description' => 'Run TuxCMS on your own server. Every feature, no limits, and the published site is yours.', 'features' => ['Every section type and theme', 'Careers and the inbox', 'Unlimited pages and editors', 'Community help'], 'cta_label' => 'Read the setup guide', 'url' => '/features/publishing/'],
                        ['name' => 'Hosted', 'summary' => 'For one business', 'price' => '£29', 'period' => '/month', 'description' => 'We run it, back it up and keep it updated. You only edit and press Publish.', 'features' => ['Everything in Self-hosted', 'Hosting, backups and updates', 'Your own domain with HTTPS', 'Email support within a day'], 'cta_label' => 'Start the free trial', 'url' => '/contact/'],
                        ['name' => 'Studio', 'summary' => 'For agencies with clients', 'price' => '£99', 'period' => '/month', 'description' => 'Up to ten client sites, each with its own dashboard, and a theme made for each.', 'features' => ['Everything in Hosted, for ten sites', 'A theme built for each client', 'One login across every site', 'A named person to call'], 'cta_label' => 'Talk to us', 'url' => '/contact/'],
                    ],
                ],
            ],
            [
                'key' => 'integrations', 'type' => 'integrations', 'title' => 'Works with what you already use',
                'content' => 'Built on Laravel, sending mail through the usual services, and publishing plain files to any host that serves them.',
                'data' => [
                    'cta_label' => 'How publishing works',
                    'url' => '/features/publishing/',
                    'items' => $this->integrationItems(),
                ],
            ],
            [
                'key' => 'team', 'type' => 'team', 'title' => 'Who you will talk to',
                'content' => 'A small team, and the same people answer the support inbox.',
                'data' => [
                    'members' => [
                        ['name' => 'Ada Okafor', 'role' => 'Product and design', 'bio' => 'Decides what goes in the dashboard, and mostly what stays out.'],
                        ['name' => 'Tom Reyes', 'role' => 'Engineering', 'bio' => 'Keeps publishing fast. Cares far too much about load times.'],
                        ['name' => 'Sofia Bianchi', 'role' => 'Support', 'bio' => 'Answers every question herself, and turns the common ones into features.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Frequently asked questions',
                'data' => [
                    'items' => [
                        ['question' => 'Do I need a developer to change my site?', 'answer' => 'No. Words, photographs, sections and pages are all edited in the dashboard. A developer is only needed to make a new theme.'],
                        ['question' => 'Where does the site run?', 'answer' => 'Anywhere that serves files. The published site is plain HTML, CSS and images, so ordinary shared hosting is plenty.'],
                        ['question' => 'What happens to spam?', 'answer' => 'It is held in the inbox, marked with the reason, and never emailed on. You can release anything genuine with one click.'],
                        ['question' => 'Can I undo a change?', 'answer' => 'Yes. Every save keeps a revision, so any page can go back to how it was.'],
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
                'key' => 'stats', 'type' => 'stats', 'title' => 'What it adds up to',
                'data' => [
                    'items' => [
                        ['label' => 'Parts in one dashboard', 'value' => '9'],
                        ['label' => 'Section types built in', 'value' => '11'],
                        ['label' => 'Roles to share the work', 'value' => '4'],
                        ['label' => 'Login pages on the public site', 'value' => '0'],
                    ],
                ],
            ],
            [
                // The template's vision and mission, each beside a photograph.
                'key' => 'aims', 'type' => 'features', 'title' => 'Where we are going',
                'content' => 'Two things we hold every change to.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Vision', 'description' => 'Every small business edits its own website, the moment something changes, without asking anyone.', 'image' => $this->photos['identity-on-a-desk']],
                        ['icon' => 'star', 'title' => 'Mission', 'description' => 'Make the page you edit the page you get, and keep what visitors load fast, cheap and hard to break.', 'image' => $this->photos['laptop-and-notebook']],
                    ],
                ],
            ],
            [
                'key' => 'team', 'type' => 'team', 'title' => 'The people',
                'data' => [
                    'members' => [
                        ['name' => 'Ada Okafor', 'role' => 'Product and design', 'bio' => 'Decides what goes in the dashboard, and mostly what stays out.'],
                        ['name' => 'Tom Reyes', 'role' => 'Engineering', 'bio' => 'Keeps publishing fast. Cares far too much about load times.'],
                        ['name' => 'Sofia Bianchi', 'role' => 'Support', 'bio' => 'Answers every question herself, and turns the common ones into features.'],
                        ['name' => 'Kwame Mensah', 'role' => 'Themes', 'bio' => 'Draws the themes, and makes sure no editor can break one.'],
                        ['name' => 'Lena Fischer', 'role' => 'Hosting', 'bio' => 'Runs the hosted plan, its backups and its updates.'],
                        ['name' => 'Ravi Patel', 'role' => 'Search', 'bio' => 'Looks after the titles, structured data and sitemap every publish writes.'],
                    ],
                ],
            ],
            [
                'key' => 'integrations', 'type' => 'integrations', 'title' => 'What we build on',
                'content' => 'Open tools we trust, and the hosts the sites we make end up on.',
                'data' => ['cta_label' => 'See every feature', 'url' => '/features/', 'items' => $this->integrationItems()],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'Come and say hello',
                'content' => 'Tell us about the site you want to stop dreading.',
                'data' => ['primary_cta' => ['label' => 'Get in touch', 'url' => '/contact/']],
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
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
            [
                // The template's "Everything you need" grid: one wide card, then a row.
                'key' => 'offerings', 'type' => 'features', 'title' => 'Everything in the dashboard',
                'content' => 'Nine parts, each with a page of its own. All of them in one place.',
                'data' => [
                    'columns' => 3,
                    'items' => [
                        ['icon' => 'compass', 'title' => 'Editing on the page', 'description' => 'Click the words on your real page and type. Every save is kept as a revision.', 'url' => '/features/editing/', 'link_label' => 'Editing'],
                        ['icon' => 'zap', 'title' => 'One-button publishing', 'description' => 'The whole site written out as fast static files, now or on a date you set.', 'url' => '/features/publishing/', 'link_label' => 'Publishing'],
                        ['icon' => 'briefcase', 'title' => 'Careers', 'description' => 'Roles with closing dates, and applications with their CVs.', 'url' => '/features/careers/', 'link_label' => 'Careers'],
                        ['icon' => 'mail', 'title' => 'An inbox with a spam filter', 'description' => 'Every form message in one place, with sales pitches held back and the reason given.', 'url' => '/features/inbox/', 'link_label' => 'Inbox'],
                        ['icon' => 'image', 'title' => 'A media library', 'description' => 'Upload a photograph once and use it anywhere, with its alt text beside it.', 'url' => '/features/media-library/', 'link_label' => 'Media library'],
                        ['icon' => 'search', 'title' => 'Search and SEO', 'description' => 'Titles, descriptions, social cards, structured data and a sitemap for every page.', 'url' => '/features/search-and-seo/', 'link_label' => 'Search and SEO'],
                        ['icon' => 'layers', 'title' => 'Themes and sections', 'description' => 'Pages built from sections, drawn by a theme editors cannot break.', 'url' => '/features/themes-and-sections/', 'link_label' => 'Themes and sections'],
                        ['icon' => 'users', 'title' => 'Users and roles', 'description' => 'Invite your team, each with a role that decides what they may change.', 'url' => '/features/users-and-roles/', 'link_label' => 'Users and roles'],
                        ['icon' => 'star', 'title' => 'Site settings', 'description' => 'Name, logo, contact details and legal pages, changed once for every page.', 'url' => '/features/site-settings/', 'link_label' => 'Site settings'],
                    ],
                ],
            ],
            [
                // The template's "Features that fit your workflow": who it is for.
                'key' => 'fit', 'type' => 'features', 'title' => 'Made for the sites that go stale',
                'content' => 'The businesses we built TuxCMS for, and what each gets from it.',
                'data' => [
                    'columns' => 3,
                    'items' => [
                        ['icon' => 'users', 'title' => 'For clinics and practices', 'description' => 'Opening hours, prices and the team page change often. Now the front desk changes them.', 'image' => $this->photos['room-by-the-windows']],
                        ['icon' => 'pen-tool', 'title' => 'For studios and agencies', 'description' => 'Hand a client a site they can edit without breaking the design you made for them.', 'image' => $this->photos['studio-table']],
                        ['icon' => 'image', 'title' => 'For shops and services', 'description' => 'Swap a photograph or a price in the morning and it is live before you open.', 'image' => $this->photos['kit-laid-out']],
                    ],
                ],
            ],
            [
                'key' => 'integrations', 'type' => 'integrations', 'title' => 'Works with what you already use',
                'content' => 'Built on Laravel, sending mail through the usual services, and publishing plain files to any host that serves them.',
                'data' => ['cta_label' => 'How publishing works', 'url' => '/features/publishing/', 'items' => $this->integrationItems()],
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
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See every feature on one site',
                'content' => 'The demo dashboard runs this site. Sign in and try any of it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
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
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                // The template's service details: a photograph, then what it does.
                'key' => 'body', 'type' => 'text', 'title' => 'How it feels',
                'data' => ['image' => $this->photos['desk-from-above'], 'image_alt' => 'A desk seen from above'],
                'content' => "Open a page and you see it exactly as visitors will. Click a heading and type over it. Drag a section to move it, or add a new one from the list.\n\nNothing goes live until you press Publish, and every save is kept as a revision.",
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'What you can change',
                'content' => 'Everything a visitor sees, from the same screen.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'layers', 'title' => 'Sections', 'description' => 'Add a section from the list, drag it into place, or hide it until it is ready.'],
                        ['icon' => 'compass', 'title' => 'Pages and the menu', 'description' => 'Nest a page under another and the menu follows. Choose which pages show in it.'],
                        ['icon' => 'image', 'title' => 'Photographs', 'description' => 'Pick one from the media library, with its alt text already written.'],
                        ['icon' => 'search', 'title' => 'How it shows in search', 'description' => 'Set the title and description each page shows in search results, and see it before you publish.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Questions about editing',
                'data' => [
                    'items' => [
                        ['question' => 'Who can edit?', 'answer' => 'Anyone you invite, with a role that decides what they may change. Each save is kept as a revision with who made it.'],
                        ['question' => 'What if I make a mistake?', 'answer' => 'Nothing is live until you publish, and any earlier revision can be restored.'],
                        ['question' => 'Can I break the design?', 'answer' => 'No. You change the words, photographs and order of sections; the theme keeps the layout.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'Change a headline yourself',
                'content' => 'Sign in to the demo, click any words and type over them.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
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
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Before you write',
                'data' => [
                    'items' => [
                        ['question' => 'How soon will you reply?', 'answer' => 'Within one working day. A person reads every message; nothing goes to a bot.'],
                        ['question' => 'Can you move my current site over?', 'answer' => 'Yes. Send us its address and we will tell you what moves across as it is and what needs a theme.'],
                        ['question' => 'Can I try it before I talk to anyone?', 'answer' => 'Yes. The demo dashboard runs this site; sign in and change anything you like.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'Rather try it first?',
                'content' => 'The demo dashboard edits this very site. No sign-up.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
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
                'data' => ['image' => $this->photos['printed-pages'], 'image_alt' => 'Printed pages and magazines laid out'],
                'content' => "A static page has nothing to run when someone opens it, so it is fast everywhere and costs almost nothing to host.\n\nThere is no login page on the public site and no database for anyone to reach.",
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'What a publish does',
                'content' => 'One press, and the whole site is written out again.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Writes every page', 'description' => 'Each published page becomes a plain HTML file, with its stylesheet and photographs beside it.'],
                        ['icon' => 'search', 'title' => 'Writes what search engines read', 'description' => 'Titles, descriptions, structured data and the sitemap, for every page.'],
                        ['icon' => 'briefcase', 'title' => 'Writes the careers pages', 'description' => 'Open roles get a page each, and closed ones come down by themselves.'],
                        ['icon' => 'calendar', 'title' => 'Waits when you tell it to', 'description' => 'A page dated for next week is left out until its day, then goes live on its own.'],
                    ],
                ],
            ],
            [
                'key' => 'stats', 'type' => 'stats', 'title' => 'What static buys you',
                'data' => [
                    'items' => [
                        ['label' => 'Database queries when someone visits', 'value' => '0'],
                        ['label' => 'Second, at most, to publish this demo site', 'value' => '1'],
                        ['label' => 'Login pages on the public site', 'value' => '0'],
                        ['label' => 'Host that serves files can run it', 'value' => 'Any'],
                    ],
                ],
            ],
            [
                'key' => 'integrations', 'type' => 'integrations', 'title' => 'Publish to any host',
                'content' => 'The published site is plain files, so it runs on whatever serves them.',
                'data' => ['cta_label' => 'Ask about hosting', 'url' => '/contact/', 'items' => $this->integrationItems()],
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

        // A page for each of the CMS's features, under Features.
        $featurePages = [];
        $featurePages[] = $this->makePage([
            'title' => 'Careers',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 2,
            'excerpt' => 'Post roles, set closing dates and read applications with their CVs.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Hiring, on your own site',
                'content' => 'Post a role, set the day it closes, and read every application with the CV attached.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'A careers page that runs itself',
                'data' => ['image' => $this->photos['room-by-the-windows'], 'image_alt' => 'People working beside tall windows'],
                'content' => "Each open role gets its own page with an application form. When its closing date passes, the page comes down by itself on the next publish.\n\nApplications arrive in the dashboard with the CV attached, and anything that looks like spam is set aside for you to check.",
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'What hiring looks like',
                'content' => 'Everything from the post to the shortlist.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'briefcase', 'title' => 'Roles with closing dates', 'description' => 'Draft a role, publish it, and it closes on the day you choose.'],
                        ['icon' => 'pen-tool', 'title' => 'An application form per role', 'description' => 'Name, email, a cover note and a CV, sent straight to the dashboard.'],
                        ['icon' => 'shield', 'title' => 'Spam set aside', 'description' => 'Sales pitches dressed as applications are held, not emailed, and can be released in one click.'],
                        ['icon' => 'mail', 'title' => 'Every applicant in one list', 'description' => 'Filter by role, open the CV, and see what was held back and why.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Questions about careers',
                'data' => [
                    'items' => [
                        ['question' => 'Where do the CVs go?', 'answer' => 'They are kept in the dashboard with the application, for signed-in staff only.'],
                        ['question' => 'What happens when a role closes?', 'answer' => 'It leaves the careers page on the next publish. Its applications stay in the dashboard.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Careers | TuxCMS',
            'meta_description' => 'Post roles with closing dates and read applications with their CVs in the TuxCMS dashboard.',
            'robots' => 'index, follow',
        ]);

        $featurePages[] = $this->makePage([
            'title' => 'Inbox',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 3,
            'excerpt' => 'Every contact form message in one place, with spam held back.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Every message, none of the junk',
                'content' => 'Contact forms land in one inbox. Sales pitches are held back with the reason, never thrown away.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'A spam filter that explains itself',
                'data' => ['image' => $this->photos['laptop-and-notebook'], 'image_alt' => 'A laptop and an open notebook'],
                'content' => "The filter judges intent, not single words: a message is only held when it both names something being sold and shows the sender is selling it.\n\nNothing is rejected. A held message is kept with its reason, so a wrong call is one click to undo.",
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'How it sorts your mail',
                'content' => 'Three outcomes, each one you can see.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'mail', 'title' => 'Sent on, or flagged', 'description' => 'A genuine message is emailed to you. An uncertain one is emailed too, marked so you look twice.'],
                        ['icon' => 'shield', 'title' => 'Held, with the reason', 'description' => 'A sales pitch is kept in the inbox, not emailed, with the reason it was held and one click to release it.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Questions about the inbox',
                'data' => [
                    'items' => [
                        ['question' => 'Can a real message be held by mistake?', 'answer' => 'It can, which is why nothing is deleted. Release it and it is treated like any other.'],
                        ['question' => 'Does it stop bots?', 'answer' => 'Forms carry a token. A message sent without one, or faster than a person could type it, is held; one from a page left open over a week is flagged.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Inbox | TuxCMS',
            'meta_description' => 'One inbox for every contact form, with a spam filter that holds sales pitches back and says why.',
            'robots' => 'index, follow',
        ]);

        $featurePages[] = $this->makePage([
            'title' => 'Media library',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 4,
            'excerpt' => 'Upload a photograph once and use it on any page.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Every photograph in one place',
                'content' => 'Upload once, pick it from any page, and keep its alt text beside it.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'Upload once, use anywhere',
                'data' => ['image' => $this->photos['kit-laid-out'], 'image_alt' => 'Tools and devices laid out in rows'],
                'content' => "Photographs live in one library instead of being scattered across pages. Pick one in the editor and it drops into the section you are working on.\n\nIts alt text travels with it, so every page that uses it describes it the same way.",
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'What the library keeps',
                'content' => 'Made for the people who change the pictures.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'image', 'title' => 'Every upload, with its alt text', 'description' => 'Photographs and files in one list, each with the description every page will use.'],
                        ['icon' => 'layers', 'title' => 'One pick per section', 'description' => 'Choose a picture from the library straight from the page you are editing.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Media library | TuxCMS',
            'meta_description' => 'One media library for every photograph on the site, with alt text kept beside each.',
            'robots' => 'index, follow',
        ]);

        $featurePages[] = $this->makePage([
            'title' => 'Search and SEO',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 5,
            'excerpt' => 'Titles, descriptions, social cards and a sitemap for every page.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Found, and shown properly',
                'content' => 'Set how each page looks in search results and when it is shared, and see it before you publish.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'What each page carries',
                'content' => 'Written out on every publish.',
                'data' => [
                    'columns' => 3,
                    'items' => [
                        ['icon' => 'search', 'title' => 'Search and social previews', 'description' => 'Edit the title, description and sharing picture, and see the result before you publish.'],
                        ['icon' => 'code', 'title' => 'Structured data', 'description' => 'Your business, each page, its breadcrumbs and its questions, marked up for search engines.'],
                        ['icon' => 'compass', 'title' => 'A sitemap and robots file', 'description' => 'Every published page is listed, and drafts never are.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Questions about SEO',
                'data' => [
                    'items' => [
                        ['question' => 'Do I need a plugin?', 'answer' => 'No. Every field is in the page settings, and publishing writes them out.'],
                        ['question' => 'Can I keep a page out of search?', 'answer' => 'Yes. Set it to noindex and it is left out of the sitemap too.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Search and SEO | TuxCMS',
            'meta_description' => 'Per-page titles, descriptions, social cards, structured data and a sitemap, written on every publish.',
            'robots' => 'index, follow',
        ]);

        $featurePages[] = $this->makePage([
            'title' => 'Themes and sections',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 6,
            'excerpt' => 'Pages built from sections, drawn by a theme you can change.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Pages made of sections',
                'content' => 'Eleven built-in section types, plus any a theme adds, each one edited where it sits.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'body', 'type' => 'text', 'title' => 'Your design, kept safe',
                'data' => ['image' => $this->photos['identity-on-a-desk'], 'image_alt' => 'A laptop and a book on a white desk'],
                'content' => "A theme draws the site; editors fill it in. They change words, pictures and the order of sections, and the design stays exactly as it was made.\n\nA theme can add section types of its own, like the pricing and integrations on this site, and they appear in the editor like any other.",
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'Sections, built in and added',
                'content' => 'Mix them in any order on any page.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'layers', 'title' => 'Eleven built-in sections', 'description' => 'Hero, text, features, stats, testimonials, team, questions, call to action, contact, form and map.'],
                        ['icon' => 'sparkles', 'title' => 'Types a theme adds', 'description' => 'A theme can declare its own, like the pricing and integrations here, and they join the Add section menu.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Themes and sections | TuxCMS',
            'meta_description' => 'TuxCMS pages are built from sections, drawn by a theme that editors cannot break.',
            'robots' => 'index, follow',
        ]);

        $featurePages[] = $this->makePage([
            'title' => 'Users and roles',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 7,
            'excerpt' => 'Invite your team, each with a role that fits.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'The right access for each person',
                'content' => 'Invite the people who edit, and give each a role that decides what they may change.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'Four roles',
                'content' => 'From running the site to reading it.',
                'data' => [
                    'columns' => 1,
                    'items' => [
                        ['icon' => 'users', 'title' => 'Four roles, from admin to subscriber', 'description' => 'Admins run everything; editors publish; authors write for an editor to publish; subscribers only view.'],
                    ],
                ],
            ],
            [
                'key' => 'faq', 'type' => 'faq', 'title' => 'Questions about access',
                'data' => [
                    'items' => [
                        ['question' => 'Can I see who changed a page?', 'answer' => 'Yes. Each revision records who saved it and when.'],
                        ['question' => 'Is the dashboard on the public site?', 'answer' => 'No. The published site is plain files with no login page; the dashboard runs apart from it.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Users and roles | TuxCMS',
            'meta_description' => 'Invite your team to TuxCMS with roles that decide what each person may change.',
            'robots' => 'index, follow',
        ]);

        $featurePages[] = $this->makePage([
            'title' => 'Site settings',
            'template' => 'default',
            'status' => 'published',
            'parent_id' => $features->id,
            'show_in_nav' => true,
            'order' => 8,
            'excerpt' => 'Name, logo, contact details, social links and legal pages in one place.',
        ], [
            [
                'key' => 'hero', 'type' => 'hero', 'title' => 'Change it once, everywhere',
                'content' => 'The details every page repeats live in one place: change them there and the whole site follows.',
                'data' => ['alignment' => 'left', 'primary_cta' => ['label' => 'Try it in the demo', 'url' => '/admin/']],
            ],
            [
                'key' => 'what', 'type' => 'features', 'title' => 'What settings hold',
                'content' => 'A few fields each, changed once for every page.',
                'data' => [
                    'columns' => 2,
                    'items' => [
                        ['icon' => 'star', 'title' => 'The site and how to reach it', 'description' => 'Name, logo, contact email, phone and address, shown wherever the theme uses them.'],
                        ['icon' => 'pen-tool', 'title' => 'The contact form', 'description' => 'Choose its fields and where its messages are emailed.'],
                    ],
                ],
            ],
            [
                'key' => 'cta', 'type' => 'cta', 'title' => 'See it in the dashboard',
                'content' => 'The demo dashboard runs this site. Sign in and try it.',
                'data' => ['primary_cta' => ['label' => 'Open the demo dashboard', 'url' => '/admin/']],
            ],
        ], [
            'meta_title' => 'Site settings | TuxCMS',
            'meta_description' => 'One place for the name, logo, contact details, social links, form email and legal pages.',
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

        return [$home, $about, $features, $editing, $publishing, ...$featurePages, $contact, $privacy, $release, $stories];
    }

    /**
     * Put the demo photographs in the media library, where the Media screen
     * lists them and an editor can pick them for any section.
     */
    /**
     * The services TuxCMS really works with, by their own marks (Simple Icons).
     *
     * @return array<int, array{name: string, logo: string, url: string}>
     */
    private function integrationItems(): array
    {
        return [
            ['name' => 'Laravel', 'logo' => '/themes/shopfront/media/logos/laravel.svg', 'url' => 'https://laravel.com'],
            ['name' => 'PHP', 'logo' => '/themes/shopfront/media/logos/php.svg', 'url' => 'https://www.php.net'],
            ['name' => 'MySQL', 'logo' => '/themes/shopfront/media/logos/mysql.svg', 'url' => 'https://www.mysql.com'],
            ['name' => 'SQLite', 'logo' => '/themes/shopfront/media/logos/sqlite.svg', 'url' => 'https://sqlite.org'],
            ['name' => 'Resend', 'logo' => '/themes/shopfront/media/logos/resend.svg', 'url' => 'https://resend.com'],
            ['name' => 'Amazon SES', 'logo' => '/themes/shopfront/media/logos/amazonwebservices.svg', 'url' => 'https://aws.amazon.com/ses/'],
            ['name' => 'OpenStreetMap', 'logo' => '/themes/shopfront/media/logos/openstreetmap.svg', 'url' => 'https://www.openstreetmap.org'],
            ['name' => 'Netlify', 'logo' => '/themes/shopfront/media/logos/netlify.svg', 'url' => 'https://www.netlify.com'],
            ['name' => 'Cloudflare Pages', 'logo' => '/themes/shopfront/media/logos/cloudflarepages.svg', 'url' => 'https://pages.cloudflare.com'],
            ['name' => 'GitHub Pages', 'logo' => '/themes/shopfront/media/logos/githubpages.svg', 'url' => 'https://pages.github.com'],
            ['name' => 'Vercel', 'logo' => '/themes/shopfront/media/logos/vercel.svg', 'url' => 'https://vercel.com'],
            ['name' => 'Nginx', 'logo' => '/themes/shopfront/media/logos/nginx.svg', 'url' => 'https://nginx.org'],
            ['name' => 'Apache', 'logo' => '/themes/shopfront/media/logos/apache.svg', 'url' => 'https://httpd.apache.org'],
        ];
    }

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
    /**
     * A blog in every state the dashboard shows: tagged published posts, one
     * scheduled for next week and one still a draft.
     */
    private function seedPosts(): array
    {
        $posts = [
            [
                'slug' => 'what-a-booking-flow-taught-us', 'title' => 'What a booking flow taught us about patience',
                'status' => 'published', 'published_at' => now()->subDays(3), 'tags' => ['Client work', 'Research'],
                'cover_image' => $this->photos['laptop-and-notebook'], 'cover_image_alt' => 'A laptop and an open notebook on a desk',
                'excerpt' => 'We spent four months on Fathom\'s booking flow. Most of what worked came from slowing down.',
                'body' => '<p>When Fathom asked us to rebuild how people book a trip, the brief was one line long: fewer people should give up halfway. Four months later the number who finish a booking is up by a third.</p>'
                    .'<h2>Watch before you draw</h2><p>We sat with twelve people while they planned a real trip. Nobody booked in one sitting. They compared dates, asked a partner, came back the next day. The old flow treated every pause as a failure and emptied the basket.</p>'
                    .'<h2>What we changed</h2><ul><li>A booking now keeps itself for a week, with no account needed.</li><li>Prices are shown in full from the first screen.</li><li>The form asks for a name and an email, then stops asking.</li></ul>'
                    .'<blockquote><p>I could finally leave it and come back. That was the whole thing for me.</p></blockquote>'
                    .'<p>None of this is new. It took watching real people to see which of the obvious things mattered. If you are planning similar work, <a href="/contact/">tell us about it</a>.</p>',
            ],
            [
                'slug' => 'a-brand-book-people-read', 'title' => 'A brand book people actually read',
                'status' => 'published', 'published_at' => now()->subDays(12), 'tags' => ['Client work', 'Brand'],
                'cover_image' => $this->photos['printed-pages'], 'cover_image_alt' => 'Printed pages laid out on a table',
                'excerpt' => 'Overlap\'s guidelines are twelve pages long. That was the hardest part.',
                'body' => '<p>Most brand guidelines are written once and opened never. Overlap wanted theirs used by the people who make things every day, most of whom are not designers.</p>'
                    .'<h2>Twelve pages, no more</h2><p>We set a limit before writing a word. Anything that did not help someone make a decision this week was cut.</p>'
                    .'<ol><li>The logo, and the two ways it can go wrong.</li><li>Three colours and when each one is used.</li><li>One typeface, four sizes.</li></ol>'
                    .'<p>The book has been printed twice since. <strong>People keep it on their desks</strong>, which is the only measure we care about.</p>',
            ],
            [
                'slug' => 'how-we-run-a-first-week', 'title' => 'How we run the first week of a project',
                'status' => 'published', 'published_at' => now()->subDays(20), 'tags' => ['Studio notes', 'Process'],
                'cover_image' => $this->photos['studio-table'], 'cover_image_alt' => 'People working around a studio table',
                'excerpt' => 'Five days, one room, and a rule against slides.',
                'body' => '<p>The first week decides how the rest of a project feels. We spend it in one room with the client, and we do not make slides.</p>'
                    .'<h2>Monday to Wednesday</h2><p>We listen. Everyone who will live with the result gets an hour, from the founder to the person who answers the phone.</p>'
                    .'<h2>Thursday and Friday</h2><p>We write down what we heard on one sheet of paper and read it back. If the room disagrees with the sheet, we have saved months.</p>',
            ],
            [
                'slug' => 'eleven-years-in', 'title' => 'Eleven years in',
                'status' => 'published', 'published_at' => now()->subDays(34), 'tags' => ['Studio notes'],
                'excerpt' => 'A short note on staying small.',
                'body' => '<p>Lumen Studio turned eleven this month. We are nine people, the same size we were at six.</p><p>Staying small was a choice. It means the person you meet first is the person who does the work, and that we say no more often than we would like.</p><p>Thank you to everyone who has trusted us with something they care about.</p>',
            ],
            [
                'slug' => 'opening-our-doors-in-spring', 'title' => 'Opening our doors in spring',
                'status' => 'published', 'published_at' => now()->addDays(7), 'tags' => ['Studio notes'],
                'cover_image' => $this->photos['room-by-the-windows'], 'cover_image_alt' => 'A bright room with tall windows',
                'excerpt' => 'We are holding an open studio day. This post is scheduled, so it is not on the site yet.',
                'body' => '<p>For one day in spring the studio is open to anyone who wants to see how we work. Details will follow here.</p>',
            ],
            [
                'slug' => 'notes-on-pricing', 'title' => 'Notes on pricing our work',
                'status' => 'draft', 'published_at' => null, 'tags' => [],
                'excerpt' => 'An unfinished draft.',
                'body' => '<p>A draft that is still being written. Drafts are never built into the site.</p>',
            ],
        ];

        return array_map(function (array $post) {
            $saved = Post::updateOrCreate(
                ['slug' => $post['slug']],
                [...Arr::except($post, 'tags'), 'author_id' => $this->author->id],
            );
            $saved->syncTagsWithType($post['tags'], Post::TAG_TYPE);

            return $saved;
        }, $posts);
    }

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

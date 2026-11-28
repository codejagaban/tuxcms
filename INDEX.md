# TuxCMS code map

What lives where, and what each piece is for. See [README.md](README.md) for the
shape of the system and [DEPLOYMENT.md](DEPLOYMENT.md) for putting it on a
server.

## Models

`app/Models`

| Model | Holds |
| --- | --- |
| `Page` | A page: title, slug, template, status, parent, navigation flags, custom fields. Slugs are unique among siblings, so `/about/design` and `/services/design` can both exist. Soft deletes. |
| `PageSection` | One ordered block of a page, typed (`hero`, `features`, `faq` and the rest) with its own JSON data. |
| `PageRevision` | A snapshot of a page, written as edits are saved so earlier states can be restored. |
| `JobPost` | A vacancy on the careers pages. |
| `JobApplication` | An application against a vacancy, with the uploaded CV. |
| `Enquiry` | A contact form submission. |
| `MediaLibrary` | Site-wide uploads that belong to no single page. |
| `Seo` | Polymorphic SEO record: meta, Open Graph, Twitter, schema, canonical, robots. |
| `Setting` | Key and value site settings, grouped for the dashboard. |
| `User` | An editor. Accounts are made with `artisan user:create`; there is no public registration. |

## Services

`app/Services`

- `Site/SiteBuilder` writes the static site and prunes files it previously wrote.
- `Site/NavigationBuilder` builds the site navigation from pages and settings.
- `Seo/SitemapGenerator`, `RobotsGenerator`, `SchemaGenerator`, `LlmsTxtGenerator` produce `sitemap.xml`, `robots.txt`, JSON-LD and `llms.txt`.
- `Content/PageRevisionService` records and restores page revisions.
- `Media/ThemeMediaImporter` pulls a theme's photographs into the library.

`app/Support`

- `Site` resolves the active theme, the installed themes and the page templates.
- `SpamFilter` issues and checks the signed token every public form carries.
- `PublishState` tracks whether the generated site is behind the content.
- `HeadSanitizer` and `CssMinifier` clean and compress what the build emits.

## API

`routes/api.php`, controllers in `app/Http/Controllers/Api/V1`. Everything is
under `/api/v1`. Writes need a Sanctum token.

**Open:** `GET health`, `POST auth/login` (throttled), `GET pages`,
`pages/tree`, `pages/homepage`, `pages/resolve`, `pages/{slugOrId}`,
`GET search`, `GET form-token`, `POST job-applications`, `POST enquiries`.

**Authenticated:** page writes and `pages/{page}/revisions`; the job posts,
applications and enquiries lists, CV download and the two `release` endpoints
for submissions the spam filter held by mistake; per-page SEO; settings; media;
and `site/status` with `site/publish`.

`GET /pages/{page}/preview` renders a page for the editor, with markers saying
which element shows which field. Every page is edited that way, in an iframe.

## Dashboard

`dashboard/src`

- `pages/` one screen each: Dashboard, Pages, PageEditor, Jobs, Applications, Enquiries, Media, SEO, Settings, Login.
- `components/editor/SitePreview.jsx` makes the live site preview editable in place: it marks the editable text and images, outlines them, judges each outline against the colour behind it, and wires photo replacement and preview navigation.
- `components/editor/Inspector.jsx` is the right-hand panel: page fields, the section list, per-section fields and SEO.
- `lib/icons.jsx` every icon, Phosphor at one weight plus the marks drawn for this dashboard.
- `lib/theme.js` the light, dark and system appearance choice.

Built with `npm --prefix dashboard run build`, which outputs to `public/admin`.

## Database

`database/migrations` holds 17 migrations: the Laravel tables, then pages,
page sections, SEO, settings, media libraries, jobs, page revisions, job
applications, enquiries and the spam reason column, plus the Spatie permission,
media library and tag tables.

Seeders cover the demo content, roles and the default settings. One client's
site content is kept apart in `database/seeders/Clients`; it is not part of
the CMS.

## Commands

| Command | Does |
| --- | --- |
| `site:build` | Regenerates the static site. `--quiet-progress` for cron. |
| `user:create` | Makes an editor account. |
| `media:import-theme` | Imports a theme's photographs into the library. `--hand-built` also gathers those in a theme's hand-built pages. |
| `spam:prune` | Deletes held submissions older than 30 days. |

`routes/console.php` schedules a build every fifteen minutes, a nightly rebuild
at 03:30 and the spam prune at 03:10.

## Tests

`tests/Feature` holds 20 files and 145 tests: publishing and the generated
output, page paths and reserved slugs, revisions, SEO, navigation, enquiries,
job posts and applications, settings, media and theme imports.

`ThemeMarkerContractTest` checks that every installed theme and the base views
mark every field they show.

`dashboard/tests/e2e` holds the Playwright specs for the page editor and the
applications screen, including an axe accessibility check.

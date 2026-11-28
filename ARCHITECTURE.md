# TuxCMS architecture

How the system is put together and why. [README.md](README.md) is the short
version, [INDEX.md](INDEX.md) is the map of files, and
[API_DOCUMENTATION.md](API_DOCUMENTATION.md) is the endpoint reference.

## The shape of it

TuxCMS runs one install per website. Three parts share a single domain:

```
~/tuxcms/
  app/  routes/  config/        the Laravel API
  dashboard/                    the React editor, built into public/admin
  resources/views/site/         the Blade views the static site is rendered from
  public/                       the document root
    index.html  about/index.html  ...   generated, served as files
    admin/                              the dashboard, built
    build/                              compiled site CSS
    storage -> ../storage/app/public    uploaded media
    index.php                           Laravel, reached only by /api and /up
```

`public/.htaccess` passes a request to `index.php` only when it matches no real
file or directory. Reading the site therefore starts no PHP at all. Writing to
it goes through `/api/v1`, and `/up` is the health check.

This is the central trade. A request for a page costs a file read rather than a
framework boot and a database round trip, and the site stays up even if the
database does not. The price is that nothing is live until it is published, so
publishing has to be cheap and safe. It is both.

## Publishing

`App\Services\Site\SiteBuilder` renders the whole site. It is called from
`php artisan site:build`, from `POST /api/v1/site/publish` behind the
dashboard's Publish button, and from the scheduler.

The build runs synchronously. A small business site renders in well under a
second, and shared hosting rarely has a dependable queue worker, so waiting is
both simpler and more honest than reporting "queued" and hoping.

Three properties make it safe to run against a live document root:

**It renders to a temporary directory first.** Everything is written under
`storage/app/site-build-<unique>`, then each file is moved into place with an
atomic rename. A render that throws half way leaves the live site untouched.

**It keeps a manifest.** `.site-manifest.json` records every file the previous
build wrote, with a hash. Files that are no longer produced are removed; files
the build never wrote are never touched. That is what keeps `site:build` from
deleting the dashboard, the compiled CSS or the media symlink.

**It refuses reserved slugs.** `config/site.php` lists `admin`, `api`, `build`,
`storage` and `up`. Each is a real directory in the document root, so a page at
`/admin` would otherwise write `admin/index.html` straight over the dashboard.

A build emits a page per published page, the careers listing and a page per
published vacancy, `sitemap.xml`, `robots.txt`, `llms.txt` and `404.html`.

Absolute URLs come from `config('site.url')`, because the build runs on the
command line where there is no request host. Asset URLs stay root relative, so
moving the site to another domain does not break them.

### Scheduled publishing

Nothing on a static site notices that a `published_at` has matured, because
there is no request to notice it. `routes/console.php` therefore runs a build
every fifteen minutes, a full rebuild nightly at 03:30, and prunes held spam at
03:10. That needs one cron entry calling `schedule:run`; without it, scheduled
content never goes live.

## Content

A page is a row in `pages` plus an ordered list of `page_sections`. Sections
are typed (`hero`, `features`, `stats`, `testimonials`, `team`, `faq`, `cta`,
`contact`, `contact_form`, `map`, `text`) and each carries a JSON `data` blob
for the fields its type needs. Rendering a page means rendering its sections in
order through the matching Blade view.

### The page tree and the path column

Pages nest through `parent_id`. The full path is kept in a materialized `path`
column rather than derived on read, because walking the parent chain is an N+1
on any listing and unusable for the builder.

`App\Observers\PageObserver` maintains it, and the ordering is deliberate:

- Path computation happens in `saved()`, not `saving()`. Spatie's `HasSlug`
  generates the slug during the save, so in `saving()` the slug is still null
  on create and every path would collapse to `/`.
- Before computing, the loaded `parent` relation is unset. Otherwise a page
  that has just been moved is handed back its old parent and keeps its old
  path.
- A change to `slug`, `parent_id` or `is_homepage` repaths every descendant.
- Only one page can be the homepage, enforced in `saving()`.

Slugs are unique among siblings, not globally, matching the
`unique(parent_id, slug)` constraint. Without that scope Spatie would suffix
globally and turn `/about/design` into `/about/design-1` just because
`/services/design` already exists.

### Saving sections

`PageController::syncSections` reconciles the incoming list against what is
stored, matching on section id so rows are updated in place. Two details are
load bearing. An id is only reused when it genuinely belongs to this page, so a
client cannot hijack another page's section by id. And removals delete models
one at a time rather than through a mass delete, because a mass delete fires no
model events and would leave every attached media file orphaned in storage.

### Revisions

Every update snapshots the page before and after. If they differ, the before
state is stored in `page_revisions` by
`App\Services\Content\PageRevisionService`. The editor lists the most recent 30
and can restore any of them, which is what makes inline editing safe to do
directly on the live preview.

## The live preview

Every page is edited on the real rendered site rather than on a mock-up of
it, whatever its template and whether or not a site theme is set: a page with
no theme renders through the base views in `resources/views/site/sections`,
which are marked up like any theme. A new page has nothing to render until it
is saved, so the editor asks for that first.

The dashboard asks `GET /api/v1/pages/{page}/preview` for the page as the CMS
renders it, which works for drafts and scheduled pages too, and shows that in
an iframe. If the request fails, the editor says so and offers to try again.
`dashboard/src/components/editor/SitePreview.jsx` prepares it:

- It collapses the theme's split-character heading animation back to one plain
  text node, so a heading does not appear to contain its own text twice.
- It makes each element carrying a field marker (below) editable in place,
  saving to exactly the section and field the marker names. An element
  without a marker is not edited, unless it is a hand-built page's written-in
  copy, offered as an override.
- Editable text and images carry a resting dashed outline, firming on hover and
  focus, so what can be edited is visible without hunting for it.
- Outline colour is judged per element against what is actually behind it. A
  one pixel canvas reads the computed background colour up the ancestor chain;
  where the element sits on a dark band the outline is drawn in white. Elements
  are judged up front and again once images and fonts have settled.
- Photos get a Change photo button positioned over them, kept in place as the
  page loads images and resizes.
- Internal links are rewired to open that page in the editor instead of
  navigating.

A section added, removed, moved, hidden or shown appears once the page is
saved, autosave included: the editor renders the preview again whenever a save
changes that shape. Changes to words need no new render, as they are made on
the preview itself, or written into it from the panel.

### Field markers

The editor used to find editable text by matching each field's value against
the page, which failed on duplicate strings and on any text a template
reshapes. Templates instead say exactly which element shows which field, with
three Blade directives from `App\Support\Preview`:

```blade
<section @tuxsection($section)>
    <h2 @field($section, 'title')>{{ $section->title }}</h2>
    <img src="{{ $d['image'] }}" alt="" @image($section, 'data.image')>
</section>
```

They print `data-tuxcms-field="{section id}:{path}"` and its siblings only
while `PageController::preview()` renders a page, inside `Preview::during()`.
The static build never switches them on, so the published site carries none;
where a marker was, a tag keeps a single space. The editor makes each marked
element edit exactly the field it names, and nothing else is guessed: a field
a template does not mark is edited from the inspector.

Shopfront marks every field it shows. The one exception is a contact form's
field labels, which carry an "(optional)" note beside the stored text, so they
are edited from the inspector.

Crystal's hand-built pages print their section data, with the wording they
used to have written in as the fallback, and mark it. Copy that has no section
data at all is edited through overrides, which are kept by position on the
page.

## SEO

`App\Traits\HasSeo` attaches a polymorphic `Seo` record. In practice it is
always a page: the endpoints are `GET` and `PUT /pages/{page}/seo`, and the
older `model_type` API pointed at Post and Category models that no longer
exist.

Four generators run at build time, in `app/Services/Seo`: `SitemapGenerator`
(full paths, excluding noindex pages, with honest `lastmod`), `RobotsGenerator`
(which disallows everything outside production), `SchemaGenerator` (JSON-LD)
and `LlmsTxtGenerator`.

## Forms and spam

The careers form and the contact forms post to `/api/v1/job-applications` and
`/api/v1/enquiries`, both throttled to five requests a minute. Shopfront's
contact form section uses the enquiries endpoint by default and hands over to
Web3Forms only when an access key is set.

Both forms carry a hidden `website` field that people never see; a request
that fills it fails validation outright. Everything else goes to
`App\Support\SpamFilter`, which returns one of three verdicts. It weighs the
age of a signed `form_token` issued when the form page was opened (anything
under three seconds was not filled in by a person, anything over a week is
stale), the script the message is written in, link count, and selling
language. Enquiries additionally catch the same long message arriving
twice, which is a mass mailing rather than a person.

The handling is the point. A submission judged spam is still stored and still
shown in the dashboard, with the reason recorded, but never emailed. The sender
gets the same thank you either way, so they learn nothing about the filter. An
uncertain verdict is delivered as normal and flagged. A wrong call is undone
from the dashboard with the `release` endpoints.

Delivery failures do not lose anything, because the submission is written
before the email is attempted and the row records `delivery_status`.

## Settings and publish state

`settings` is a key and value store, grouped for the dashboard's tabs. Two keys
are internal: `content_changed_at` and `last_published_at`, written through
`App\Support\PublishState`. Every write that affects output calls
`PublishState::markChanged()`, which is how the dashboard knows the generated
site is behind the content and shows the Publish button as pending.

## Themes

A theme is a directory of Blade views under
`resources/views/site/themes/<name>` with static assets in
`public/themes/<name>`. Adding the directory is all it takes for the name to be
accepted as a page template and for its pages to be edited on the live preview.
`SITE_THEME` picks the theme used for pages with no template-specific view, for
the careers pages and for the 404. Every page that theme renders is edited on
the live preview.

A view is resolved in order: the page's own template if it names a theme, then
`SITE_THEME`, then the unthemed views in `resources/views/site`. There are no
unthemed careers views, so those fall back to Shopfront when `SITE_THEME` is
unset.

Shopfront is the bundled theme and the default in `.env.example`. A theme's
stylesheet lives at `public/themes/<name>/css/site.css` and is minified beside
itself when the site is built. A theme marks each element that shows a field
with `@field`, and each picture with `@image`; an element should hold exactly
that field's text, and a field written as several paragraphs is marked on the
element that wraps them.

`tests/Feature/ThemeMarkerContractTest.php` holds every theme to this. It
renders every section type, with the defaults a new section starts with
(`dashboard/src/lib/sectionDefaults.json`, which the editor reads too), through
each installed theme and the base views, and fails naming the theme, section
type, view and field for any field shown without its marker. Text a view
deliberately leaves to the panel is listed in the test with its reason.

`php artisan media:import-theme` copies the photographs a theme's pages use
into the media library once, so editors can reuse them without hunting through
the filesystem.

## Authentication

Sanctum tokens, and nothing else. There is deliberately no public registration:
this is one install per website with a small known set of editors, and accounts
are made with `php artisan user:create`. Login is throttled to five attempts a
minute.

Read endpoints for pages carry no auth middleware, so the static builder and
the public site can work without a token. They still need to tell an editor
from a visitor, because an editor must see drafts and hidden sections. They ask
the Sanctum guard directly rather than relying on `$request->user()`, which is
always null on an unprotected route.

`EnsureJsonResponse` is prepended to the API middleware stack so every response,
including validation and error responses, is JSON regardless of what the client
sent in `Accept`.

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

### The blog

A post is a row in `posts`. Its tags are spatie/laravel-tags tags of the type
`blog` (`Post::TAG_TYPE`), so they never mix with tags used anywhere else. Its
body is HTML written in the dashboard's Tiptap editor and cleaned by
`App\Support\RichText` as it is saved, so what is stored is safe to print into
a page as it is. `RichText` is a thin allow-list over Symfony's HTML
sanitizer: headings, paragraphs, lists, quotes, code blocks, links, images and
inline emphasis survive; links to other sites gain `rel="noopener noreferrer"`
and open in a new tab.

`SiteBuilder` writes the blog only when at least one post is published and
its date has passed: `/blog/` and its further pages (`/blog/page/2/`, nine
posts to a page), `/blog/<slug>/` for each post, `/blog/tag/<slug>/` for
each tag on a published post, and the RSS feed at
`/blog/feed.xml`. With no published posts none of these exist and the menu
carries no Blog link. Posts are also listed in the sitemap and `llms.txt`,
found by `GET /api/v1/search`, and each post page carries `BlogPosting`
structured data.

The blog's pages are not `Page` records, so `SiteBuilder` hands each one an
unsaved stand-in page whose `seo` relation carries its title, description,
canonical address, share image and structured data. That lets the shared
`site.partials.head` draw their `<head>` like any other page's.

A post dated in the future is scheduled. It goes live at the next build after
that date, which the scheduled build takes care of (see Scheduled publishing).

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
  without a marker is not edited.
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
used to have written in as the fallback, and mark it. Copy and photos that
belong to no other section live in each page's `copy` section, so every piece
is edited the same way. `MoveOverridesIntoPageCopy` moves the position-based
overrides that pages used to store into it, once, after deploying.

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

### Kinds of enquiry

Each enquiry form sends a hidden `type`, its kind. Every site has `contact`;
any other kind is declared by a theme, under `enquiries` in its `theme.json`,
with a label for one and for many:

```json
"enquiries": {
  "client_review": { "label": "Client review", "plural": "Client reviews" }
}
```

`App\Support\EnquiryKinds` gathers them from the themes the site uses: its
`SITE_THEME` and any theme its pages are drawn with. A theme that is installed
but unused adds nothing, and a theme may relabel `contact`. The inbox accepts
only these kinds, and the dashboard builds its tabs, the email subjects and a
notification address per kind in Settings (`enquiry_email_<kind>`) from the
same list, so the core names no client's forms. A kind no longer declared but
still holding messages keeps its tab. Crystal declares its staff requests and
candidate registrations this way.

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
the careers and blog pages and for the 404. Every page that theme renders is edited on
the live preview.

A view is resolved in order: the page's own template if it names a theme, then
`SITE_THEME`, then the unthemed views in `resources/views/site`. There are no
unthemed careers views, so those fall back to Shopfront when `SITE_THEME` is
unset or the theme has none of its own. The blog is different: each theme
draws its own with `blog/index.blade.php` and `blog/show.blade.php`, as
Shopfront does, and a theme without them gets the plain views in
`resources/views/site/blog` set inside its own `layout.blade.php`. Those plain
views take their font and colours from the theme, so the blog still matches
the site, and they never borrow another theme's blog.

Shopfront is the bundled theme and the default in `.env.example`. A theme's
stylesheet lives at `public/themes/<name>/css/site.css` and is minified beside
itself when the site is built. A theme marks each element that shows a field
with `@field`, and each picture with `@image`; an element should hold exactly
that field's text, and a field written as several paragraphs is marked on the
element that wraps them.

`tests/Feature/ThemeMarkerContractTest.php` holds every theme to this. It
renders every built-in section type, with the defaults a new section starts
with (`resources/sections/base.json`, which the editor receives too), and every
type the theme declares itself, through each installed theme and the base
views, and fails naming the theme, section type, view and field for any field
shown without its marker. A theme's own types start from their manifest
defaults with one sample item in each empty list, and their schema says which
fields are text (marked with `@field`) and which are pictures (`@image`). Text
a view deliberately leaves to the panel is listed in the test with its reason.

### Section types and theme.json

A page may hold the built-in section types (`config/tuxcms.pages.section_types`)
plus the ones declared by the theme that renders it: the page's template theme
when it has a `page.blade.php`, else `SITE_THEME`, else none
(`Site::pageTheme()`, which the builder uses to pick the page view too).

A theme declares its types in an optional `theme.json` beside its views,
read and checked by `App\Support\ThemeManifest`:

```json
{
  "sections": {
    "<type>": {
      "label": "Packages",
      "blurb": "One line for the editor's menu.",
      "fields": { "<path>": { "type": "text", "label": "Heading" } },
      "defaults": { "title": "Packages", "content": "", "data": { "items": [] } }
    }
  }
}
```

- A type name matches `^[a-z][a-z0-9_]*$` and may not be a built-in type.
- `defaults` are required: they are what "Add section" creates.
- A field's path is `title`, `content` or `data.<key>[.<key>…]`, the same paths
  the markers name. A field has a `type` and a `label`, and may have
  `required`, `placeholder`, `default` (what a new list item starts with) and
  `inspector: false` (left out of the side panel because it is edited on the
  preview) or `preview: false` (the page never shows it as plain text an editor
  could type over, such as an image's description or formatted rich text, so
  it is edited in the side panel, carries no marker, and the contract test does
  not ask for one).
- Field types: `text`, `textarea`, `richtext`, `url`, `image`, `number`,
  `boolean`, `select` (with `options`: strings, or `{value, label}` where a
  value may be a number) and `list` (with `item`: an object of fields keyed by
  name, or the name of a single-value type). Lists may nest.

A bad manifest throws `InvalidThemeManifest`, naming the theme, the type and
the problem; nothing is overridden or skipped silently. Parsed manifests are
cached for the process, keyed by file, size and time.

`StorePageRequest` and `UpdatePageRequest` accept a section type only when the
page's renderer provides it, and check a theme type's fields against its
schema: required fields, url (a web address or a path, anchor, `mailto:` or
`tel:` link), number and boolean values, select options and list shapes. The
built-in types keep their existing rules. A template change sent without the
sections that would leave stored sections on a theme that cannot draw them is
a `422` on `template` naming those sections; sent with them, each is named on
its own `type`. Nothing is dropped.

The built-in types are described the same way in `resources/sections/base.json`
(it lives outside `dashboard/` because a release ships without it), and
`GET /api/v1/section-types?template=` returns them followed by the theme's. The
editor builds its "Add section" menu, new sections and side panel from that
list, and fetches it again when the page's template changes. A theme type with
no view in its theme renders the theme's fallback and logs a warning while
previewed.

`tests/Fixtures/views/site/themes/schematest` is a fixture theme with one
declared type, used by the manifest, API, endpoint, rendering and contract
tests.

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

# Tasks

## Active

- [ ] **Give Crystal's remaining written-in copy section data** - 108 pieces of copy on Crystal's hand-built pages (43 on About, 28 on Home) have no section data and are edited only through overrides, which are kept by position and break if the template's markup changes. Moving that copy into section data would let overrides be retired.
- [ ] **Remove Crystal's unused section views** - `resources/views/site/themes/crystal/sections/` (9 files) is never rendered: a Crystal page uses a hand-built view, or falls back to the base views in `resources/views/site/sections/`.
- [ ] **Move the Crystal client site out of this repository** - its templates and 52 MB of assets (`resources/views/site/themes/crystal`, `public/themes/crystal`), its seeders (`database/seeders/Clients/Crystal`) and its test are a client's website, in a public repository. They need a home of their own and a way to be installed onto that client's server.

## Waiting On


## Someday

## Done

- [x] ~~**Replace text matching in the preview with field markers** - templates mark which element shows which field, only in preview renders; every page is edited on the rendered preview; the text matcher is gone; `ThemeMarkerContractTest` checks every theme marks every field it shows, using the editor's own section defaults~~ (2026-11-28)
- [x] ~~**Keep inline edits made after an autosave** - every save gave the sections new client ids while the preview kept the old ones, so the next edit made on the page was dropped; ids now survive a save~~ (2026-11-28)
- [x] ~~**Stop Crystal edits from being silently lost** - the 58 pieces of copy written into Crystal's templates with an unused copy in the section data now print that data and carry markers, and text matching is off on hand-built pages; published pages are unchanged~~ (2026-11-28)
- [x] ~~**Stop the core naming one client** - the editor's preview is `SitePreview`, overrides are offered to any theme with hand-built pages, the client's media importer is an option on the theme importer, footer link lists follow each site's settings, and the client's seeders are fenced off~~ (2026-11-27)
- [x] ~~**Fill out the Overview** - recent pages as a gallery, an inbox of the latest messages and applications, vacancies and the newest media~~ (2026-11-27)
- [x] ~~**Add a gallery view to Pages** - each page as a small live picture of itself, with the choice remembered~~ (2026-11-27)
- [x] ~~**Give the dashboard motion** - a highlight that slides between items, screens and dialogs that rise out of a blur, sections that slide when reordered; written with Motion~~ (2026-11-27)
- [x] ~~**Preview pages that are not on the site yet** - drafts and scheduled pages are rendered for the editor instead of showing the 404 page, and scheduled pages are labelled as such~~ (2026-11-27)
- [x] ~~**Give Shopfront photographs and motion** - photo layouts for the hero, text and features sections, seven demo photographs seeded into the media library, and motion written with Motion; the demo keeps its uploads apart from the real media library~~ (2026-11-27)
- [x] ~~**Make the editable outlines easier to spot** - blue rather than grey, lighter on dark bands~~ (2026-11-27)
- [x] ~~**Make the demo show the whole CMS** - Shopfront's contact form now delivers to the Enquiries inbox, and the demo content covers every section type, nested services, draft and scheduled pages, vacancies in each status and a sample inbox with held spam~~ (2026-11-27)
- [x] ~~**Put Shopfront on the dashboard's colours** - the theme uses the CMS's paper and ink tokens and follows the visitor's light or dark setting~~ (2026-11-27)
- [x] ~~**Drop Crystal as the careers fallback** - careers views now fall back to Shopfront; the Crystal client site needs `SITE_THEME=crystal` on its server to keep its own careers pages~~ (2026-11-27)
- [x] ~~**Decide what happens to `feat/fine-flow-care`** - deleted; its hooks were superseded by the theme system~~ (2026-11-27)
- [x] ~~**Give the CMS a theme of its own** - Shopfront renders every section type, the careers pages and the 404, is the default in `.env.example`, and `bin/demo.sh` runs it on throwaway data~~ (2026-11-27)
- [x] ~~**Replace the three stale documents** - rewrite `ARCHITECTURE.md` and `API_DOCUMENTATION.md` from the current code, and turn `SETUP_COMPLETE.md` into a working `SETUP.md`~~ (2026-11-27)
- [x] ~~**Show which parts of the preview can be edited** - draw a resting outline on every editable region and judge each one against the colour behind it, so editable text and images are visible without hovering~~ (2026-11-27)
- [x] ~~**Give section names the full row** - the four row actions held 182px of the 340px inspector open, leaving 67px for a name; they now sit over the row and appear on hover~~ (2026-11-27)
- [x] ~~**Repair the dashboard end-to-end suite** - the specs had looked for an iframe title that changed on 2026-11-11 and never set the `live_preview` flag the editor now reads~~ (2026-11-27)
- [x] ~~**Clear the dashboard lint errors** - teach the linter about the icon factories, and close the account menu during render rather than from an effect~~ (2026-11-27)
- [x] ~~**Dress the dashboard** - dark mode on a black page with neutral greys, Phosphor icons at one weight, colours drawn from tokens, Uncut Sans, frosted bars and overlays, status pills, a boot screen and skeleton loaders~~ (2026-11-24)
- [x] ~~**Edit Fine Flow pages on the real site preview** - a page whose template is an installed theme is edited on the rendered site rather than through plain fields~~ (2026-11-11)
- [x] ~~**Upgrade job applications from email links to a proper form** - support validation, CV upload, spam protection, delivery feedback, and the configured recipient~~ (2026-09-16)
- [x] ~~**Remove or ignore local deployment artifacts** - keep generated deployment archives out of source control~~ (2026-09-16)
- [x] ~~**Reduce the dashboard JavaScript bundle** - split infrequently used editor and administration screens~~ (2026-09-16)
- [x] ~~**Add privacy and legal content support** - provide policy pages and footer links for forms that collect personal information~~ (2026-09-16)
- [x] ~~**Complete an accessibility and responsive-editor audit** - verify keyboard editing, focus, image alternatives, contrast, and narrow-screen behaviour~~ (2026-09-16)
- [x] ~~**Protect unfinished editor changes** - add autosave, unsaved-change warnings, and recoverable page revisions~~ (2026-09-16)
- [x] ~~**Make global navigation and footer content manageable** - let editors maintain site-wide links and service directories without a code deployment~~ (2026-09-16)
- [x] ~~**Add browser regression coverage for the visual editor** - protect inline editing, publishing, image replacement, and editable navigation from recurring regressions~~ (2026-09-16)
- [x] ~~**Connect the Crystal footer to global CMS settings** - render configured contact and social details, and never show dead placeholder links~~ (2026-09-16)

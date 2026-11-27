# Tasks

## Active

- [ ] **Settle the Fine Flow theme** - `.env.example` defaults `SITE_THEME` to `fineflow`, but no fineflow views are committed. `Site::themes()` lists whatever directories exist under `resources/views/site/themes`, so a fresh clone has no such theme and the setting points at nothing. Either commit the theme or drop the default.

## Waiting On

- [ ] **Decide what happens to `feat/fine-flow-care`** - two commits from 2026-11-07 adding `config/fine-flow.php`, a `FineFlowContentSeeder` and publishing hooks, now 76 commits behind `main`. Its worktree at `../fine-flow-care-preview` is gone from disk and shows as prunable.

## Someday

## Done

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

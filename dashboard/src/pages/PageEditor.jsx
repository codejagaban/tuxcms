import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Monitor, DeviceMobile, GlobeHemisphereWest, ClockCounterClockwise } from '../lib/icons';
import toast from 'react-hot-toast';
import { apiErrorMessage, pageAPI, siteAPI } from '../lib/api';
import { makeUid, createSection } from '../lib/editorSchema';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import Spinner from '../components/ui/Spinner';
import SectionRenderer from '../components/editor/SectionRenderer';
import Inspector from '../components/editor/Inspector';
import SitePreview from '../components/editor/SitePreview';
import MediaPicker from '../components/editor/MediaPicker';
import RevisionHistory from '../components/editor/RevisionHistory';

// Give every section a stable client-side id for React keys + selection,
// independent of the database id (new sections don't have one yet).
const withUids = (sections = []) =>
  [...sections]
    .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    .map((s) => ({ ...s, _uid: makeUid(), data: s.data || {} }));

// After a save the server hands the sections back. Each keeps the client id
// it had, matched by position (they come back in the order they were sent),
// so anything still pointing at a section, such as an element in the preview,
// keeps pointing at the same one. Giving them new ids lost the next edit made
// on the page.
const keepUids = (previous = [], fresh = []) => withUids(fresh).map((section, index) => {
  const before = previous[index];
  const same = before && (before.id == null || String(before.id) === String(section.id));
  return same ? { ...section, _uid: before._uid } : section;
});

const setNestedValue = (source, path, value) => {
  const keys = path.split('.');
  const root = Array.isArray(source) ? [...source] : { ...(source || {}) };
  let cursor = root;

  keys.forEach((key, index) => {
    if (index === keys.length - 1) {
      cursor[key] = value;
      return;
    }
    const next = cursor[key];
    cursor[key] = Array.isArray(next) ? [...next] : { ...(next || {}) };
    cursor = cursor[key];
  });

  return root;
};


// A theme with hand-built pages has text and pictures that belong to no
// section. Edits to those are kept beside the page as overrides, which the
// theme applies when it builds. `crystal_overrides` is the name the first
// such site stored them under; it is still read, and rewritten on save.
const templateOverrides = (content) => {
  try {
    const parsed = JSON.parse(content || '{}');
    return parsed.template_overrides || parsed.crystal_overrides || {};
  } catch {
    return {};
  }
};

const withTemplateOverride = (content, kind, key, value) => {
  let parsed = {};
  try { parsed = JSON.parse(content || '{}'); } catch { parsed = {}; }
  const { crystal_overrides: legacy, ...rest } = parsed;
  const overrides = parsed.template_overrides || legacy || {};
  return JSON.stringify({
    ...rest,
    template_overrides: {
      ...overrides,
      [kind]: { ...(overrides[kind] || {}), [key]: value },
    },
  });
};

export default function PageEditor() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isNew = !id;

  const [page, setPage] = useState(null);
  const [selectedUid, setSelectedUid] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [dirty, setDirty] = useState(false);
  const [device, setDevice] = useState(() => window.innerWidth < 768 ? 'mobile' : 'desktop');
  const [publishing, setPublishing] = useState(false);
  const [previewRevision, setPreviewRevision] = useState(() => Date.now());
  // Which page the answer below is for, and the markup to show when that
  // page has no built file (null when it has one).
  const [preview, setPreview] = useState({ id: null, html: null });
  const [imageEdit, setImageEdit] = useState(null);
  const [editablePages, setEditablePages] = useState([]);
  const [historyOpen, setHistoryOpen] = useState(false);
  const [lastSavedAt, setLastSavedAt] = useState(null);

  // The preview is the page as the CMS renders it for the editor, which
  // carries the field markers that say exactly which element shows which
  // field. It also exists for drafts and scheduled pages, which have no built
  // page. Only if it cannot be fetched does the editor fall back to the built
  // page, and to finding fields by their text.
  const previewId = page?.id;
  const previewLive = Boolean(page?.live_preview);
  const previewPath = !page ? null
    : page.is_homepage ? '/' : `/${String(page.path || page.slug || '').replace(/^\/+|\/+$/g, '')}/`;
  useEffect(() => {
    if (!previewId || !previewLive || !previewPath) return undefined;
    let current = true;
    pageAPI.preview(previewId)
      .then((res) => res.data)
      .catch(() => null)
      .then((html) => { if (current) setPreview({ id: previewId, html: typeof html === 'string' ? html : null }); });
    return () => { current = false; };
  }, [previewId, previewLive, previewPath, previewRevision]);

  // ── Load ────────────────────────────────────────────────
  useEffect(() => {
    let active = true;
    (async () => {
      try {
        setLoading(true);

        if (isNew) {
          const hero = createSection('hero');
          if (active) {
            setPage({ title: 'Untitled page', slug: '', status: 'draft', template: 'default', is_homepage: false, excerpt: '', content: '', seo: {}, sections: [hero] });
            setSelectedUid(hero._uid);
          }
        } else {
          const res = await pageAPI.get(id);
          const data = res.data.data;
          if (active) {
            const sections = withUids(data.sections);
            setPage({ ...data, seo: data.seo || {}, sections });
            setSelectedUid(sections[0]?._uid || null);
          }
        }
      } catch (e) {
        console.error(e);
        toast.error('Could not load this page');
      } finally {
        if (active) setLoading(false);
      }
    })();
    return () => { active = false; };
  }, [id, isNew]);

  useEffect(() => {
    let active = true;
    pageAPI.list({ per_page: 100 })
      .then((response) => {
        if (active) setEditablePages(response.data.data || []);
      })
      .catch((error) => console.error('Could not load editable navigation targets:', error));
    return () => { active = false; };
  }, []);

  // ── Mutators (all mark the page dirty) ──────────────────
  const mutate = useCallback((fn) => {
    setPage((prev) => (prev ? fn(prev) : prev));
    setDirty(true);
  }, []);

  const updatePage = (patch) => mutate((p) => ({ ...p, ...patch }));
  const updateSeo = (patch) => mutate((p) => ({ ...p, seo: { ...(p.seo || {}), ...patch } }));

  const updateSection = (uid, patch) =>
    mutate((p) => ({ ...p, sections: p.sections.map((s) => (s._uid === uid ? { ...s, ...patch } : s)) }));

  const updateData = (uid, data) =>
    mutate((p) => ({ ...p, sections: p.sections.map((s) => (s._uid === uid ? { ...s, data } : s)) }));

  const updatePreviewField = (uid, field, value) => {
    if (field === 'title' || field === 'content') {
      updateSection(uid, { [field]: value });
      return;
    }
    if (!field.startsWith('data.')) return;
    mutate((p) => ({
      ...p,
      sections: p.sections.map((section) => section._uid === uid
        ? { ...section, data: setNestedValue(section.data, field.slice(5), value) }
        : section),
    }));
  };

  const addSection = (type) => {
    const s = createSection(type);
    mutate((p) => ({ ...p, sections: [...p.sections, s] }));
    setSelectedUid(s._uid);
  };

  const removeSection = (uid) =>
    mutate((p) => {
      const sections = p.sections.filter((s) => s._uid !== uid);
      if (uid === selectedUid) setSelectedUid(sections[0]?._uid || null);
      return { ...p, sections };
    });

  const moveSection = (uid, dir) =>
    mutate((p) => {
      const idx = p.sections.findIndex((s) => s._uid === uid);
      const target = idx + dir;
      if (idx < 0 || target < 0 || target >= p.sections.length) return p;
      const sections = [...p.sections];
      [sections[idx], sections[target]] = [sections[target], sections[idx]];
      return { ...p, sections };
    });

  /** Regenerate the static site so the saved changes go live. */
  const runPublish = useCallback(async () => {
    setPublishing(true);
    try {
      await siteAPI.build();
      setPreviewRevision(Date.now());
      // Let the Publish button refresh its "pending changes" state.
      window.dispatchEvent(new Event('tuxcms:published'));
      toast.success('Published to the live site');
    } catch (e) {
      toast.error(apiErrorMessage(e, 'Saved, but publishing failed'));
    } finally {
      setPublishing(false);
    }
  }, []);

  // ── Save ────────────────────────────────────────────────
  /**
   * @param {{publish?: boolean}} options When publishing, a draft is promoted
   *   to published first — otherwise Publish would appear to do nothing,
   *   since the builder only writes published pages.
   */
  const save = useCallback(async ({ publish = false, silent = false } = {}) => {
    if (!page || saving) return;
    setSaving(true);

    const status = publish ? 'published' : (page.status || 'draft');

    try {
      const payload = {
        title: page.title,
        slug: page.slug || undefined,
        content: page.content ?? '',
        excerpt: page.excerpt ?? '',
        template: page.template || 'default',
        status,
        is_homepage: !!page.is_homepage,
        show_in_nav: page.show_in_nav !== false,
        nav_label: page.nav_label || null,
        sections: page.sections.map((s, i) => ({
          // Sending the id lets the server update rows in place, so section
          // ids stay stable and attached media isn't orphaned on every save.
          id: s.id ?? null,
          key: s.key || s.type,
          type: s.type,
          title: s.title ?? '',
          content: s.content ?? '',
          data: s.data || {},
          order: i,
          is_visible: s.is_visible !== false,
        })),
        seo: page.seo && Object.keys(page.seo).length ? page.seo : undefined,
      };

      if (isNew) {
        const res = await pageAPI.create(payload);
        if (!silent) toast.success('Page created');
        setDirty(false);
        setLastSavedAt(new Date());
        navigate(`/dashboard/pages/${res.data.data.id}/edit`, { replace: true });
      } else {
        const res = await pageAPI.update(id, payload);
        const data = res.data.data;
        const sections = keepUids(page.sections, data.sections);
        // Preserve which section was selected across the reload by position.
        const prevIdx = page.sections.findIndex((s) => s._uid === selectedUid);
        setPage({ ...data, seo: data.seo || {}, sections });
        setSelectedUid(sections[prevIdx]?._uid || sections[0]?._uid || null);
        setDirty(false);
        setLastSavedAt(new Date());
        if (!silent) toast.success('Changes saved');
      }

      // Only publish once the save actually succeeded.
      if (publish) {
        await runPublish();
        // The published page has just been rebuilt; show the fresh copy.
        setPreviewRevision(Date.now());
      }
    } catch (e) {
      console.error(e);
      toast.error(apiErrorMessage(e, silent ? 'Autosave failed' : 'Save failed'));
    } finally {
      setSaving(false);
    }
  }, [page, saving, isNew, id, selectedUid, navigate, runPublish]);

  // Cmd/Ctrl+S to save
  const saveRef = useRef(save);
  saveRef.current = save;
  useEffect(() => {
    const onKey = (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        saveRef.current();  // plain save; publishing is deliberate
      }
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, []);

  // Debounced background save. Every successful update first captures the
  // previous server state as a revision, so autosave never removes recovery.
  useEffect(() => {
    if (!dirty || isNew || saving || publishing) return undefined;
    const timer = window.setTimeout(() => saveRef.current({ silent: true }), 3000);
    return () => window.clearTimeout(timer);
  }, [page, dirty, isNew, saving, publishing]);

  // Warn before leaving with unsaved changes.
  useEffect(() => {
    const onBeforeUnload = (e) => {
      if (dirty) { e.preventDefault(); e.returnValue = ''; }
    };
    window.addEventListener('beforeunload', onBeforeUnload);
    return () => window.removeEventListener('beforeunload', onBeforeUnload);
  }, [dirty]);

  if (loading) {
    return (
      <div className="flex h-dvh items-center justify-center bg-[var(--color-paper)]">
        <Spinner size="lg" />
      </div>
    );
  }
  if (!page) {
    return (
      <div className="flex h-dvh flex-col items-center justify-center gap-4 bg-[var(--color-paper)]">
        <p className="text-[var(--color-muted)]">Page not found.</p>
        <Button variant="ghost" onClick={() => navigate('/dashboard/pages')}>Back to pages</Button>
      </div>
    );
  }

  const selected = page.sections.find((s) => s._uid === selectedUid) || null;
  const canvasWidth = device === 'mobile' ? 'max-w-[420px]' : 'max-w-[1100px]';
  // Themes that build real pages are edited on the page itself, so what the
  // editor shows is what the site shows.
  // The API says which templates are installed themes.
  const liveCanvas = Boolean(page.live_preview);

  const sectionPreview = page.sections.map((section) => {
    const isSel = section._uid === selectedUid;
    return (
      <div
        key={section._uid}
        onMouseDown={() => setSelectedUid(section._uid)}
        className={`group relative ${section.is_visible === false ? 'opacity-40' : ''}`}
      >
        <div
          aria-hidden="true"
          className={`pointer-events-none absolute inset-0 z-20 transition-colors ${
            isSel ? 'ring-2 ring-inset ring-[var(--color-ink)]' : 'ring-0 ring-inset ring-[var(--color-ink)] group-hover:ring-1'
          }`}
        />
        {section.is_visible === false && (
          <div className="absolute top-2 right-2 z-30"><Badge variant="warning">Hidden</Badge></div>
        )}
        <SectionRenderer
          section={section}
          update={(patch) => updateSection(section._uid, patch)}
          updateData={(data) => updateData(section._uid, data)}
        />
      </div>
    );
  });

  return (
    <div className="flex h-dvh flex-col bg-[var(--color-paper)]">
      {/* Top bar */}
      <header className="flex min-h-16 shrink-0 items-center gap-3 border-b border-[var(--color-rule-2)] bg-[var(--color-paper)] px-3 sm:px-4">
        <button
          onClick={() => {
            if (dirty && !window.confirm('Discard unsaved changes?')) return;
            navigate('/dashboard/pages');
          }}
          className="icon-button"
          aria-label="Back to pages"
          title="Back to pages"
        >
          <ArrowLeft className="h-5 w-5" weight="bold" />
        </button>

        <div className="min-w-0">
          <div className="flex items-center gap-2">
            <span className="font-semibold text-[var(--color-ink)] truncate">{page.title || 'Untitled'}</span>
            {page.is_scheduled
              ? <Badge variant="pending">Scheduled</Badge>
              : <Badge variant={page.status === 'published' ? 'published' : 'draft'}>{page.status || 'draft'}</Badge>}
            {dirty && <span className="h-2 w-2 rounded-full bg-[var(--color-warn)]" title="Unsaved changes" />}
          </div>
          <div className="text-xs text-[var(--color-muted)] truncate">
            {publishing
              ? 'Publishing to the live site…'
              : saving
                ? 'Saving changes…'
                : lastSavedAt
                  ? `Saved ${lastSavedAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`
                  : `/${page.slug || '…'}`}
          </div>
        </div>

        <div className="ml-auto flex items-center gap-2">
          {!isNew && (
            <button className="icon-button" type="button" onClick={() => setHistoryOpen(true)} title="Page history" aria-label="Page history">
              <ClockCounterClockwise className="h-5 w-5" />
            </button>
          )}
          <div className="hidden items-center rounded-md border border-[var(--color-rule-2)] p-0.5 sm:flex">
            <button
              onClick={() => setDevice('desktop')}
              className={`icon-button min-h-9 min-w-9 ${device === 'desktop' ? 'bg-[var(--color-paper-3)] text-[var(--color-ink)]' : 'text-[var(--color-muted)]'}`}
              title="Desktop"
              aria-label="Preview on desktop"
              aria-pressed={device === 'desktop'}
            >
              <Monitor className="h-4 w-4" />
            </button>
            <button
              onClick={() => setDevice('mobile')}
              className={`icon-button min-h-9 min-w-9 ${device === 'mobile' ? 'bg-[var(--color-paper-3)] text-[var(--color-ink)]' : 'text-[var(--color-muted)]'}`}
              title="Mobile"
              aria-label="Preview on mobile"
              aria-pressed={device === 'mobile'}
            >
              <DeviceMobile className="h-4 w-4" />
            </button>
          </div>
          {/* One action: save, then regenerate the static site. A draft is
              promoted to published, since "publish" should mean the page
              actually appears on the site. ⌘S still saves without publishing,
              for work you're not ready to make live. */}
          <Button
            variant="primary"
            size="sm"
            onClick={() => save({ publish: true })}
            isLoading={saving || publishing}
            title={
              page.status === 'published'
                ? 'Save and update the live site'
                : 'Save, publish this page, and update the live site'
            }
          >
            <GlobeHemisphereWest className="h-4 w-4" weight="bold" />
            {publishing ? 'Publishing…' : saving ? 'Saving…' : 'Publish'}
          </Button>
        </div>
      </header>

      {/* Body: preview + inspector */}
      <div className="flex min-h-0 flex-1">
        <main className={`flex-1 p-3 sm:p-4 ${liveCanvas ? 'overflow-hidden' : 'overflow-y-auto pb-[48dvh] sm:pb-[48dvh] md:pb-6'}`}>
          <div className={`mx-auto ${canvasWidth} ${liveCanvas ? 'h-full' : ''} transition-[max-width] duration-200`}>
            {/* The built-in preview draws a light website, so it keeps the
                light colours whatever the dashboard is set to. */}
            <div data-theme={liveCanvas ? undefined : 'light'} className={`overflow-hidden rounded-lg border border-[var(--color-rule-2)] bg-[var(--color-paper)] text-[var(--color-ink)] shadow-[0_4px_12px_oklch(18%_0.01_95_/_0.06)] ${liveCanvas ? 'h-full' : ''}`}>
              {page.sections.length === 0 && (
                <div className="py-24 text-center text-[var(--color-muted)]">
                  <p className="mb-3">This page has no sections yet.</p>
                  <p className="text-sm">Use <span className="font-medium text-[var(--color-muted)]">Add</span> in the Page panel to start building.</p>
                </div>
              )}
              {liveCanvas && preview.id !== page.id
                // Not yet known whether this page has a built file to show.
                ? <div className="flex h-full min-h-64 items-center justify-center"><Spinner /></div>
                : liveCanvas
                ? <SitePreview
                    path={previewPath}
                    revision={previewRevision}
                    html={preview.html}
                    mobile={device === 'mobile'}
                    sections={page.sections}
                    pages={editablePages}
                    overrides={templateOverrides(page.content)}
                    allowOverrides={Boolean(page.supports_overrides)}
                    onEdit={updatePreviewField}
                    onOverrideEdit={(kind, key, value) => {
                      mutate((current) => ({ ...current, content: withTemplateOverride(current.content, kind, key, value) }));
                    }}
                    onSelect={setSelectedUid}
                    onImageEdit={(target, element) => setImageEdit({ target, element })}
                    onPageNavigate={(pageId) => {
                      if (String(pageId) === String(id)) return;
                      if (dirty && !window.confirm('Discard unsaved changes?')) return;
                      navigate(`/dashboard/pages/${pageId}/edit`);
                    }}
                  />
                : sectionPreview}
            </div>
            {!liveCanvas && (
              <p className="text-center text-xs text-[var(--color-muted)] mt-4">
                Click any text above to edit it inline. Use the panel to manage structure.
              </p>
            )}
          </div>
        </main>

        <Inspector
          page={page}
          selected={selected}
          updatePage={updatePage}
          updateSeo={updateSeo}
          updateSection={updateSection}
          updateData={updateData}
          addSection={addSection}
          removeSection={removeSection}
          moveSection={moveSection}
          selectSection={setSelectedUid}
        />
      </div>

      <MediaPicker
        isOpen={!!imageEdit}
        currentUrl={imageEdit?.target.url}
        currentAlt={imageEdit?.target.alt}
        onClose={() => setImageEdit(null)}
        onSelect={(url, alt) => {
          const target = imageEdit?.target;
          if (!target) return;
          if (target.overrideKey != null) {
            mutate((current) => ({
              ...current,
              content: withTemplateOverride(current.content, 'images', target.overrideKey, { url, alt }),
            }));
          } else {
            updatePreviewField(target.sectionUid, target.field, url);
            if (target.altField) updatePreviewField(target.sectionUid, target.altField, alt);
          }
          if (imageEdit.element) {
            imageEdit.element.src = url;
            imageEdit.element.alt = alt;
          }
          setImageEdit(null);
        }}
      />
      <RevisionHistory
        isOpen={historyOpen}
        pageId={id}
        onClose={() => setHistoryOpen(false)}
        onRestore={(snapshot) => {
          const sections = withUids(snapshot.sections || []);
          setPage((current) => ({ ...current, ...snapshot, sections, seo: snapshot.seo || {} }));
          setSelectedUid(sections[0]?._uid || null);
          setDirty(true);
          setHistoryOpen(false);
          toast.success('Earlier version restored. Review it, then publish.');
        }}
      />
    </div>
  );
}

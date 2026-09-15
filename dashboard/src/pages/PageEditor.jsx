import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Monitor, DeviceMobile, GlobeHemisphereWest } from '@phosphor-icons/react';
import toast from 'react-hot-toast';
import { apiErrorMessage, pageAPI, siteAPI } from '../lib/api';
import { makeUid, createSection } from '../lib/editorSchema';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import Spinner from '../components/ui/Spinner';
import SectionRenderer from '../components/editor/SectionRenderer';
import Inspector from '../components/editor/Inspector';
import CrystalCanvas from '../components/editor/CrystalCanvas';
import MediaPicker from '../components/editor/MediaPicker';

// Give every section a stable client-side id for React keys + selection,
// independent of the database id (new sections don't have one yet).
const withUids = (sections = []) =>
  [...sections]
    .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    .map((s) => ({ ...s, _uid: makeUid(), data: s.data || {} }));

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

const crystalOverrides = (content) => {
  try {
    const parsed = JSON.parse(content || '{}');
    return parsed.crystal_overrides || {};
  } catch {
    return {};
  }
};

const withCrystalOverride = (content, kind, key, value) => {
  let parsed = {};
  try { parsed = JSON.parse(content || '{}'); } catch { parsed = {}; }
  const overrides = parsed.crystal_overrides || {};
  return JSON.stringify({
    ...parsed,
    crystal_overrides: {
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
  const [device, setDevice] = useState('desktop');
  const [publishing, setPublishing] = useState(false);
  const [previewRevision, setPreviewRevision] = useState(() => Date.now());
  const [imageEdit, setImageEdit] = useState(null);
  const [editablePages, setEditablePages] = useState([]);

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

  const updateCrystalField = (uid, field, value) => {
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
  const save = useCallback(async ({ publish = false } = {}) => {
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
        toast.success('Page created');
        setDirty(false);
        navigate(`/dashboard/pages/${res.data.data.id}/edit`, { replace: true });
      } else {
        const res = await pageAPI.update(id, payload);
        const data = res.data.data;
        const sections = withUids(data.sections);
        // Preserve which section was selected across the reload by position.
        const prevIdx = page.sections.findIndex((s) => s._uid === selectedUid);
        setPage({ ...data, seo: data.seo || {}, sections });
        setSelectedUid(sections[prevIdx]?._uid || sections[0]?._uid || null);
        setDirty(false);
        toast.success('Changes saved');
      }

      // Only publish once the save actually succeeded.
      if (publish) {
        await runPublish();
      }
    } catch (e) {
      console.error(e);
      toast.error(apiErrorMessage(e, 'Save failed'));
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
      <div className="flex h-dvh items-center justify-center bg-[var(--color-paper-2)]">
        <Spinner size="lg" />
      </div>
    );
  }
  if (!page) {
    return (
      <div className="flex h-dvh flex-col items-center justify-center gap-4 bg-[var(--color-paper-2)]">
        <p className="text-gray-600">Page not found.</p>
        <Button variant="ghost" onClick={() => navigate('/dashboard/pages')}>Back to pages</Button>
      </div>
    );
  }

  const selected = page.sections.find((s) => s._uid === selectedUid) || null;
  const canvasWidth = device === 'mobile' ? 'max-w-[420px]' : 'max-w-[1100px]';
  const crystalPath = page.is_homepage ? '/' : `/${page.slug}/`;

  const sectionPreview = page.sections.map((section) => {
    const isSel = section._uid === selectedUid;
    const crystal = page.template === 'crystal';
    return (
      <div
        key={section._uid}
        onMouseDown={() => setSelectedUid(section._uid)}
        className={crystal
          ? `tux-section ${isSel ? 'is-selected' : ''} ${section.is_visible === false ? 'is-hidden' : ''}`
          : `group relative ${section.is_visible === false ? 'opacity-40' : ''}`}
      >
        {!crystal && (
          <div
            aria-hidden="true"
            className={`pointer-events-none absolute inset-0 z-20 transition-colors ${
              isSel ? 'ring-2 ring-inset ring-black' : 'ring-0 ring-inset ring-black group-hover:ring-1'
            }`}
          />
        )}
        {section.is_visible === false && (
          crystal
            ? <span className="editor-hidden-label">Hidden</span>
            : <div className="absolute top-2 right-2 z-30"><Badge variant="warning">Hidden</Badge></div>
        )}
        <SectionRenderer
          section={section}
          template={page.template}
          update={(patch) => updateSection(section._uid, patch)}
          updateData={(data) => updateData(section._uid, data)}
        />
      </div>
    );
  });

  return (
    <div className="flex h-dvh flex-col bg-[var(--color-paper-2)]">
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
            <span className="font-semibold text-gray-900 truncate">{page.title || 'Untitled'}</span>
            <Badge variant={page.status === 'published' ? 'published' : 'draft'}>{page.status || 'draft'}</Badge>
            {dirty && <span className="h-2 w-2 rounded-full bg-black" title="Unsaved changes" />}
          </div>
          <div className="text-xs text-gray-400 truncate">
            {publishing ? 'Publishing to the live site…' : `/${page.slug || '…'}`}
          </div>
        </div>

        <div className="ml-auto flex items-center gap-2">
          <div className="hidden items-center rounded-md border border-[var(--color-rule-2)] p-0.5 sm:flex">
            <button
              onClick={() => setDevice('desktop')}
              className={`icon-button min-h-9 min-w-9 ${device === 'desktop' ? 'bg-[var(--color-paper-3)] text-[var(--color-ink)]' : 'text-[var(--color-muted)]'}`}
              title="Desktop"
            >
              <Monitor className="h-4 w-4" />
            </button>
            <button
              onClick={() => setDevice('mobile')}
              className={`icon-button min-h-9 min-w-9 ${device === 'mobile' ? 'bg-[var(--color-paper-3)] text-[var(--color-ink)]' : 'text-[var(--color-muted)]'}`}
              title="Mobile"
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
        <main className={`flex-1 p-3 sm:p-4 ${page.template === 'crystal' ? 'overflow-hidden' : 'overflow-y-auto pb-[48dvh] sm:pb-[48dvh] md:pb-6'}`}>
          <div className={`mx-auto ${canvasWidth} ${page.template === 'crystal' ? 'h-full' : ''} transition-[max-width] duration-200`}>
            <div className={`overflow-hidden rounded-lg border border-[var(--color-rule-2)] bg-[var(--color-paper)] shadow-[0_4px_12px_oklch(18%_0.01_95_/_0.06)] ${page.template === 'crystal' ? 'h-full' : ''}`}>
              {page.sections.length === 0 && (
                <div className="py-24 text-center text-gray-400">
                  <p className="mb-3">This page has no sections yet.</p>
                  <p className="text-sm">Use <span className="font-medium text-gray-500">Add</span> in the Page panel to start building.</p>
                </div>
              )}
              {page.template === 'crystal'
                ? <CrystalCanvas
                    path={crystalPath}
                    revision={previewRevision}
                    mobile={device === 'mobile'}
                    sections={page.sections}
                    pages={editablePages}
                    overrides={crystalOverrides(page.content)}
                    onEdit={updateCrystalField}
                    onOverrideEdit={(kind, key, value) => {
                      mutate((current) => ({ ...current, content: withCrystalOverride(current.content, kind, key, value) }));
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
            {page.template !== 'crystal' && (
              <p className="text-center text-xs text-gray-400 mt-4">
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
              content: withCrystalOverride(current.content, 'images', target.overrideKey, { url, alt }),
            }));
          } else {
            updateCrystalField(target.sectionUid, target.field, url);
            if (target.altField) updateCrystalField(target.sectionUid, target.altField, alt);
          }
          if (imageEdit.element) {
            imageEdit.element.src = url;
            imageEdit.element.alt = alt;
          }
          setImageEdit(null);
        }}
      />
    </div>
  );
}

import { useState, useEffect, useCallback, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { ArrowLeft, Monitor, Smartphone, Save, Globe } from 'lucide-react';
import toast from 'react-hot-toast';
import { pageAPI, siteAPI } from '../lib/api';
import { makeUid, createSection } from '../lib/editorSchema';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import Spinner from '../components/ui/Spinner';
import SectionRenderer from '../components/editor/SectionRenderer';
import Inspector from '../components/editor/Inspector';

// Give every section a stable client-side id for React keys + selection,
// independent of the database id (new sections don't have one yet).
const withUids = (sections = []) =>
  [...sections]
    .sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
    .map((s) => ({ ...s, _uid: makeUid(), data: s.data || {} }));

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
      // Let the Publish button refresh its "pending changes" state.
      window.dispatchEvent(new Event('tuxcms:published'));
      toast.success('Published to the live site');
    } catch (e) {
      toast.error(e.response?.data?.message || 'Saved, but publishing failed');
    } finally {
      setPublishing(false);
    }
  }, []);

  // ── Save ────────────────────────────────────────────────
  /**
   * @param {{publish?: boolean}} options When publishing, a draft is promoted
   *   to published first — otherwise "Save & publish" would appear to do
   *   nothing, since the builder only writes published pages.
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
      const msg = e.response?.data?.message || 'Save failed';
      toast.error(msg);
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
      <div className="h-screen flex items-center justify-center bg-gray-50">
        <Spinner size="lg" />
      </div>
    );
  }
  if (!page) {
    return (
      <div className="h-screen flex flex-col items-center justify-center gap-4 bg-gray-50">
        <p className="text-gray-600">Page not found.</p>
        <Button variant="ghost" onClick={() => navigate('/dashboard/pages')}>Back to pages</Button>
      </div>
    );
  }

  const selected = page.sections.find((s) => s._uid === selectedUid) || null;
  const canvasWidth = device === 'mobile' ? 'max-w-[420px]' : 'max-w-[1100px]';

  return (
    <div className="h-screen flex flex-col bg-gray-100">
      {/* Top bar */}
      <header className="h-14 shrink-0 bg-white border-b border-gray-200 flex items-center gap-3 px-4">
        <button
          onClick={() => {
            if (dirty && !window.confirm('Discard unsaved changes?')) return;
            navigate('/dashboard/pages');
          }}
          className="p-2 rounded-lg hover:bg-gray-100 text-gray-600"
          title="Back to pages"
        >
          <ArrowLeft className="h-5 w-5" />
        </button>

        <div className="min-w-0">
          <div className="flex items-center gap-2">
            <span className="font-semibold text-gray-900 truncate">{page.title || 'Untitled'}</span>
            <Badge variant={page.status === 'published' ? 'published' : 'draft'}>{page.status || 'draft'}</Badge>
            {dirty && <span className="h-2 w-2 rounded-full bg-amber-400" title="Unsaved changes" />}
          </div>
          <div className="text-xs text-gray-400 truncate">
            {publishing ? 'Publishing to the live site…' : `/${page.slug || '…'}`}
          </div>
        </div>

        <div className="ml-auto flex items-center gap-2">
          <div className="flex items-center rounded-lg border border-gray-200 p-0.5">
            <button
              onClick={() => setDevice('desktop')}
              className={`p-1.5 rounded-md ${device === 'desktop' ? 'bg-gray-100 text-gray-900' : 'text-gray-400'}`}
              title="Desktop"
            >
              <Monitor className="h-4 w-4" />
            </button>
            <button
              onClick={() => setDevice('mobile')}
              className={`p-1.5 rounded-md ${device === 'mobile' ? 'bg-gray-100 text-gray-900' : 'text-gray-400'}`}
              title="Mobile"
            >
              <Smartphone className="h-4 w-4" />
            </button>
          </div>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => save()}
            isLoading={saving && !publishing}
            disabled={(!dirty && !isNew) || publishing}
          >
            <Save className="h-4 w-4" />
            Save
          </Button>

          {/* Saves, then regenerates the static site so the change is live.
              A draft is promoted to published, since "publish" should mean
              the page actually appears on the site. */}
          <Button
            variant="primary"
            size="sm"
            onClick={() => save({ publish: true })}
            isLoading={publishing}
            disabled={saving && !publishing}
            title={
              page.status === 'published'
                ? 'Save and update the live site'
                : 'Save, publish this page, and update the live site'
            }
          >
            <Globe className="h-4 w-4" />
            {publishing ? 'Publishing…' : 'Save & publish'}
          </Button>
        </div>
      </header>

      {/* Body: preview + inspector */}
      <div className="flex-1 flex min-h-0">
        <main className="flex-1 overflow-y-auto p-6">
          <div className={`mx-auto ${canvasWidth} transition-all duration-200`}>
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
              {page.sections.length === 0 && (
                <div className="py-24 text-center text-gray-400">
                  <p className="mb-3">This page has no sections yet.</p>
                  <p className="text-sm">Use <span className="font-medium text-gray-500">Add</span> in the Page panel to start building.</p>
                </div>
              )}
              {page.sections.map((section) => {
                const isSel = section._uid === selectedUid;
                return (
                  <div
                    key={section._uid}
                    onMouseDown={() => setSelectedUid(section._uid)}
                    className={`group relative ${section.is_visible === false ? 'opacity-40' : ''}`}
                  >
                    {/* The outline lives in its own overlay rather than as a
                        ring on this wrapper: an inset ring is painted beneath
                        descendants, so any section with its own background
                        (hero, stats, cta) would cover it and appear to have no
                        highlight at all. */}
                    <div
                      aria-hidden="true"
                      className={`pointer-events-none absolute inset-0 z-20 transition-colors ${
                        isSel
                          ? 'ring-2 ring-inset ring-blue-500'
                          : 'ring-0 ring-inset ring-blue-300 group-hover:ring-1'
                      }`}
                    />

                    {section.is_visible === false && (
                      <div className="absolute top-2 right-2 z-30">
                        <Badge variant="warning">Hidden</Badge>
                      </div>
                    )}
                    <SectionRenderer
                      section={section}
                      update={(patch) => updateSection(section._uid, patch)}
                      updateData={(data) => updateData(section._uid, data)}
                    />
                  </div>
                );
              })}
            </div>
            <p className="text-center text-xs text-gray-400 mt-4">
              Click any text above to edit it inline. Use the panel to manage structure.
            </p>
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
    </div>
  );
}

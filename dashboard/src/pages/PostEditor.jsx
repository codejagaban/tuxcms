import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import toast from 'react-hot-toast';
import { ArrowLeft, ArrowUpRight, ClockCounterClockwise, DeviceMobile, Eye, GlobeHemisphereWest, Image as ImageIcon, Monitor, PencilSimpleLine, Trash } from '../lib/icons';
import { postAPI, postTagAPI, siteAPI, apiErrorMessage } from '../lib/api';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import { FormSkeleton } from '../components/ui/Skeleton';
import RichTextEditor from '../components/editor/RichTextEditor';
import MediaPicker from '../components/editor/MediaPicker';
import TagInput from '../components/editor/TagInput';
import RevisionHistory from '../components/editor/RevisionHistory';
import ViewSwitch from '../components/ui/ViewSwitch';
import DatePicker from '../components/ui/DatePicker';
import { dayOf, today } from '../lib/days';

const emptyPost = {
  title: '', slug: '', excerpt: '', body: '', cover_image: '', cover_image_alt: '',
  tags: [], status: 'draft', published_at: '', meta_title: '', meta_description: '',
};

const slugify = (value) => value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

const fromServer = (post) => ({
  ...emptyPost,
  ...Object.fromEntries(Object.keys(emptyPost).map((key) => [key, post[key] ?? ''])),
  tags: (post.tags || []).map((tag) => tag.name),
  published_at: dayOf(post.published_at),
});

const fieldLabel = 'text-sm font-medium text-[var(--color-ink-2)]';
const help = 'mt-1.5 text-[13px] leading-5 text-[var(--color-muted)]';

export default function PostEditor() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isNew = !id;

  const [form, setForm] = useState(emptyPost);
  const [saved, setSaved] = useState(emptyPost);
  const [live, setLive] = useState(null);
  const [loading, setLoading] = useState(!isNew);
  const [busy, setBusy] = useState('');
  const [errors, setErrors] = useState({});
  const [slugEdited, setSlugEdited] = useState(!isNew);
  const [knownTags, setKnownTags] = useState([]);
  const [pickingCover, setPickingCover] = useState(false);
  const [mode, setMode] = useState('write');
  const [historyOpen, setHistoryOpen] = useState(false);
  const [device, setDevice] = useState(() => (window.innerWidth < 768 ? 'phone' : 'desktop'));
  const [preview, setPreview] = useState({ html: '', loading: false, failed: false });

  const dirty = useMemo(() => JSON.stringify(form) !== JSON.stringify(saved), [form, saved]);

  useEffect(() => {
    postTagAPI.list()
      .then((response) => setKnownTags(response.data.data || []))
      .catch(() => { /* the post can still be tagged by hand */ });
  }, []);

  useEffect(() => {
    if (isNew) return;
    let cancelled = false;
    (async () => {
      try {
        const post = (await postAPI.get(id)).data.data;
        if (cancelled) return;
        setForm(fromServer(post)); setSaved(fromServer(post)); setLive(post);
      } catch {
        toast.error('Could not load this post');
        navigate('/dashboard/blog');
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [id, isNew, navigate]);

  // Leaving the tab with unsaved writing asks first.
  useEffect(() => {
    if (!dirty) return undefined;
    const warn = (event) => { event.preventDefault(); event.returnValue = ''; };
    window.addEventListener('beforeunload', warn);
    return () => window.removeEventListener('beforeunload', warn);
  }, [dirty]);

  const set = (name, value) => setForm((current) => ({ ...current, [name]: value }));
  const change = (event) => set(event.target.name, event.target.value);

  const changeTitle = (event) => {
    const title = event.target.value;
    setForm((current) => ({ ...current, title, ...(slugEdited ? {} : { slug: slugify(title) }) }));
  };

  const scheduled = Boolean(form.published_at) && form.published_at > today();

  /** Save with the given status. Going live, or changing a live post, rebuilds the site. */
  const save = async (status) => {
    if (!form.title.trim()) { setErrors({ title: ['Give the post a title first.'] }); return; }

    setBusy(status === 'draft' ? 'draft' : 'publish');
    setErrors({});
    const payload = {
      ...form,
      title: form.title.trim(),
      // An address the editor did not choose is left to the server, which
      // makes one from the title that no other post has.
      slug: slugEdited ? form.slug : '',
      status,
      published_at: form.published_at || null,
    };

    try {
      const response = isNew ? await postAPI.create(payload) : await postAPI.update(id, payload);
      const post = response.data.data;
      const wasLive = live?.status === 'published';
      setForm(fromServer(post)); setSaved(fromServer(post)); setLive(post); setSlugEdited(true);

      if (status === 'published' || wasLive) {
        try {
          await siteAPI.build();
          window.dispatchEvent(new Event('tuxcms:published'));
          toast.success(post.is_scheduled ? 'Scheduled' : status === 'published' ? 'Published to the live site' : 'Taken off the live site');
        } catch (error) {
          toast.error(apiErrorMessage(error, 'Saved, but publishing failed'));
        }
      } else {
        toast.success('Draft saved');
      }

      if (isNew) navigate(`/dashboard/blog/${post.id}/edit`, { replace: true });
    } catch (error) {
      setErrors(error.response?.data?.errors || {});
      toast.error(apiErrorMessage(error, 'Could not save the post'));
    } finally {
      setBusy('');
    }
  };

  // Preview draws the post as the site would, from the form as it is now,
  // so it shows unsaved writing too. It is fetched again each time it opens.
  useEffect(() => {
    if (mode !== 'preview') return undefined;
    let cancelled = false;
    setPreview((current) => ({ ...current, loading: true, failed: false }));
    postAPI.preview({
      ...form,
      id: isNew ? undefined : Number(id),
      published_at: form.published_at || null,
    })
      .then((response) => { if (!cancelled) setPreview({ html: response.data, loading: false, failed: false }); })
      .catch(() => { if (!cancelled) setPreview({ html: '', loading: false, failed: true }); });
    return () => { cancelled = true; };
    // Only opening the preview asks for it; edits are made in Write.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mode]);

  const leave = () => {
    if (dirty && !window.confirm('Discard unsaved changes?')) return;
    navigate('/dashboard/blog');
  };

  const isLive = live?.status === 'published' && !live?.is_scheduled;
  const primaryLabel = busy === 'publish'
    ? (scheduled ? 'Scheduling…' : 'Publishing…')
    : scheduled ? (live?.is_scheduled ? 'Update schedule' : 'Schedule')
      : live?.status === 'published' ? 'Update' : 'Publish';
  const error = (name) => errors[name]?.[0];
  // A problem with one tag comes back as tags.2; show it under the field.
  const tagError = Object.entries(errors).find(([key]) => key === 'tags' || key.startsWith('tags.'))?.[1]?.[0];

  return (
    <div className="post-editor flex min-h-dvh flex-col bg-[var(--color-canvas)]">
      {/* On a phone the actions drop to a row of their own, so the title keeps its room. */}
      <header className="post-editor__bar sticky top-0 z-20 flex shrink-0 flex-wrap items-center gap-x-3 border-b border-[var(--color-rule-2)] bg-[var(--color-paper)] px-3 sm:px-4">
        <button onClick={leave} className="icon-button" aria-label="Back to the blog" title="Back to the blog">
          <ArrowLeft className="h-5 w-5" weight="bold" />
        </button>
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-2">
            <span className="truncate font-semibold text-[var(--color-ink)]">{form.title || 'Untitled post'}</span>
            {live?.is_scheduled
              ? <Badge variant="pending">Scheduled</Badge>
              : <Badge variant={live?.status === 'published' ? 'published' : 'draft'}>{live?.status === 'published' ? 'Published' : 'Draft'}</Badge>}
            {dirty && <span className="h-2 w-2 shrink-0 rounded-full bg-[var(--color-warn)]" title="Unsaved changes" />}
          </div>
          <div className="font-data truncate text-[var(--color-muted)]">/blog/{form.slug || '…'}/</div>
        </div>
        <ViewSwitch
          id="post-mode"
          label="Editor mode"
          suffix=""
          view={mode}
          onChange={setMode}
          options={[['write', 'Write', PencilSimpleLine], ['preview', 'Preview', Eye]]}
        />
        <div className="flex w-full items-center justify-end gap-2 pb-2.5 sm:w-auto sm:pb-0">
          {!isNew && (
            <button type="button" className="icon-button" onClick={() => setHistoryOpen(true)} title="Post history" aria-label="Post history">
              <ClockCounterClockwise className="h-5 w-5" />
            </button>
          )}
          {isLive && (
            <a href={live.path} target="_blank" rel="noreferrer" className="hidden items-center gap-1.5 px-2 text-sm font-medium text-[var(--color-ink-2)] hover:text-[var(--color-ink)] sm:inline-flex">
              View post <ArrowUpRight className="h-4 w-4" />
            </a>
          )}
          <Button variant="secondary" disabled={!!busy || loading} isLoading={busy === 'draft'} onClick={() => save('draft')}>
            {live?.status === 'published' ? 'Unpublish' : 'Save draft'}
          </Button>
          <Button disabled={!!busy || loading} isLoading={busy === 'publish'} onClick={() => save('published')}>
            {busy !== 'publish' && <GlobeHemisphereWest className="h-4 w-4" weight="bold" />}
            {primaryLabel}
          </Button>
        </div>
      </header>

      {loading ? (
        <div className="mx-auto w-full max-w-3xl p-6"><FormSkeleton fields={4} /></div>
      ) : mode === 'preview' ? (
        <div className="flex flex-1 flex-col items-center gap-4 px-4 py-5 sm:px-6">
          <div className="flex w-full max-w-[84rem] items-center justify-between gap-3">
            <p className="text-sm text-[var(--color-muted)]">
              {live?.status === 'published' && !live?.is_scheduled ? 'How the post will look once these changes are published.' : 'How the post will look on your website. Nothing here is public yet.'}
            </p>
            <ViewSwitch
              id="post-device"
              label="Preview width"
              suffix=" width"
              view={device}
              onChange={setDevice}
              options={[['desktop', 'Desktop', Monitor], ['phone', 'Phone', DeviceMobile]]}
            />
          </div>
          <div
            className={`post-preview relative min-h-[70vh] w-full flex-1 overflow-hidden rounded-[var(--radius-l)] border border-[var(--color-rule-2)] bg-[var(--color-paper)] ${device === 'phone' ? 'max-w-[400px]' : 'max-w-[84rem]'}`}
            aria-busy={preview.loading}
          >
            {preview.failed ? (
              <p className="p-8 text-center text-sm text-[var(--color-muted)]">The preview could not be drawn. Check your connection and open it again.</p>
            ) : (
              <iframe
                title="Post preview"
                srcDoc={preview.html}
                // A picture of the post: its links and the theme's scripts stay still.
                sandbox="allow-same-origin"
                className={`absolute inset-0 block h-full w-full border-0 bg-white transition-opacity duration-200 ${preview.loading ? 'opacity-40' : ''}`}
              />
            )}
          </div>
        </div>
      ) : (
        <div className="mx-auto grid w-full max-w-[84rem] flex-1 items-start gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:gap-8 lg:py-8">
          <main className="min-w-0">
            <label htmlFor="post-title" className="sr-only">Post title</label>
            <textarea
              id="post-title"
              rows={1}
              value={form.title}
              onChange={changeTitle}
              onKeyDown={(event) => { if (event.key === 'Enter') event.preventDefault(); }}
              placeholder="Post title"
              aria-invalid={error('title') ? 'true' : undefined}
              className="post-title w-full resize-none bg-transparent text-[2rem] font-semibold leading-[1.15] tracking-[-0.03em] text-[var(--color-ink)] outline-none placeholder:text-[var(--color-muted)] sm:text-[2.5rem]"
            />
            {error('title') && <p className="mt-1 text-sm font-medium text-[var(--color-danger)]">{error('title')}</p>}
            <div className="mt-5">
              <RichTextEditor value={form.body} onChange={(body) => set('body', body)} placeholder="Write your post…" />
            </div>
          </main>

          <aside className="space-y-4 lg:sticky lg:top-24" aria-label="Post details">
            <section className="work-surface space-y-4 p-5">
              <h2 className="text-base font-semibold">Publishing</h2>
              <div>
                <span className={fieldLabel}>Publish date</span>
                <div className="mt-1.5">
                  <DatePicker label="Publish date" value={form.published_at} onChange={(day) => set('published_at', day)} placeholder="When you publish" />
                </div>
                <p className={help}>
                  {scheduled
                    ? 'This date is in the future, so the post goes live that day.'
                    : 'Leave empty to use the day you publish. Pick a future date to schedule.'}
                </p>
              </div>
              <label className="block">
                <span className={fieldLabel}>Web address</span>
                <div className="mt-1.5 flex items-center gap-1.5">
                  <span className="font-data shrink-0 text-[var(--color-muted)]">/blog/</span>
                  <input
                    name="slug" value={form.slug} maxLength={255}
                    onChange={(event) => { setSlugEdited(true); set('slug', slugify(event.target.value.replace(/\s+/g, '-')) || event.target.value.toLowerCase().replace(/[^a-z0-9-]/g, '')); }}
                    aria-invalid={error('slug') ? 'true' : undefined}
                    className={`field-control font-data min-w-0 ${error('slug') ? 'border-[var(--color-danger)]' : ''}`}
                  />
                </div>
                {error('slug')
                  ? <p className="mt-1.5 text-sm font-medium text-[var(--color-danger)]">{error('slug')}</p>
                  : !slugEdited && <p className={help}>Made from the title. If another post has it, a number is added.</p>}
              </label>
            </section>

            <section className="work-surface space-y-4 p-5">
              <h2 className="text-base font-semibold">Tags</h2>
              <div>
                <TagInput value={form.tags} onChange={(tags) => set('tags', tags)} suggestions={knownTags} />
                <p className={help}>Each tag gets its own page on the blog, listing the posts that carry it.</p>
                {tagError && <p className="mt-1.5 text-sm font-medium text-[var(--color-danger)]">{tagError}</p>}
              </div>
            </section>

            <section className="work-surface space-y-4 p-5">
              <h2 className="text-base font-semibold">Cover image</h2>
              {form.cover_image ? (
                <>
                  <img src={form.cover_image} alt="" className="aspect-[16/10] w-full rounded-[var(--radius-m)] object-cover" />
                  <label className="block">
                    <span className={fieldLabel}>Description</span>
                    <input name="cover_image_alt" value={form.cover_image_alt} onChange={change} maxLength={255} className="field-control mt-1.5" placeholder="What the picture shows" />
                    <p className={help}>Read aloud to people who cannot see the picture.</p>
                  </label>
                  <div className="flex gap-2">
                    <Button variant="secondary" size="sm" onClick={() => setPickingCover(true)}>Change</Button>
                    <Button variant="dangerGhost" size="sm" onClick={() => setForm((current) => ({ ...current, cover_image: '', cover_image_alt: '' }))}><Trash className="h-4 w-4" />Remove</Button>
                  </div>
                </>
              ) : (
                <button
                  type="button"
                  onClick={() => setPickingCover(true)}
                  className="flex aspect-[16/10] w-full flex-col items-center justify-center gap-2 rounded-[var(--radius-m)] border border-dashed border-[var(--color-rule)] text-sm text-[var(--color-muted)] hover:bg-[var(--color-paper-2)] hover:text-[var(--color-ink)]"
                >
                  <ImageIcon className="h-6 w-6" />
                  Choose a cover image
                </button>
              )}
            </section>

            <section className="work-surface space-y-4 p-5">
              <h2 className="text-base font-semibold">Summary</h2>
              <label className="block">
                <span className={fieldLabel}>Excerpt</span>
                <textarea name="excerpt" value={form.excerpt} onChange={change} maxLength={500} rows={3} className="field-control mt-1.5" placeholder="One or two sentences about the post" />
                <p className={help}>Shown under the title in the blog listing. Left empty, one is written from the opening sentences when you publish.</p>
              </label>
            </section>

            <details className="work-surface p-5">
              <summary className="cursor-pointer text-base font-semibold">Search engines</summary>
              <div className="mt-4 space-y-4">
                <label className="block">
                  <span className={fieldLabel}>Search title</span>
                  <input name="meta_title" value={form.meta_title} onChange={change} maxLength={255} className="field-control mt-1.5" placeholder={form.title || 'Uses the post title'} />
                </label>
                <label className="block">
                  <span className={fieldLabel}>Search description</span>
                  <textarea name="meta_description" value={form.meta_description} onChange={change} maxLength={500} rows={3} className="field-control mt-1.5" placeholder="Uses the excerpt" />
                  <p className={help}>{form.meta_description.length} of about 160 characters.</p>
                </label>
              </div>
            </details>
          </aside>
        </div>
      )}

      <RevisionHistory
        kind="post"
        isOpen={historyOpen}
        pageId={isNew ? null : id}
        onClose={() => setHistoryOpen(false)}
        onRestore={(snapshot) => {
          // Back into the editor, unsaved: it is saved or published as usual.
          setForm((current) => ({
            ...fromServer({ ...snapshot, tags: (snapshot.tags || []).map((name) => ({ name })) }),
            status: current.status,
          }));
          setSlugEdited(true);
          setMode('write');
          setHistoryOpen(false);
          toast.success('Earlier version restored. Review it, then save.');
        }}
      />
      <MediaPicker
        isOpen={pickingCover}
        currentUrl={form.cover_image}
        currentAlt={form.cover_image_alt}
        onClose={() => setPickingCover(false)}
        onSelect={(url, alt) => {
          setForm((current) => ({ ...current, cover_image: url, cover_image_alt: alt || current.cover_image_alt }));
          setPickingCover(false);
        }}
      />
    </div>
  );
}

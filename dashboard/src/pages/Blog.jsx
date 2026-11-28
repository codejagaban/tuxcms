import { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { IconBlog, PencilSimple, Plus, Trash } from '../lib/icons';
import { postAPI, postTagAPI, apiErrorMessage } from '../lib/api';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import { ListSkeleton } from '../components/ui/Skeleton';
import Modal, { ModalContent, ModalFooter, ModalHeader, ModalTitle } from '../components/ui/Modal';
import { Card } from '../components/ui/Card';
import { Table, TableBody, TableCell, TableHeadCell, TableHeader, TableRow } from '../components/ui/Table';
import ViewSwitch from '../components/ui/ViewSwitch';
import TagManager from '../components/editor/TagManager';
import { useSavedView } from '../lib/useSavedView';

const FILTERS = [
  { value: '', label: 'All' },
  { value: 'published', label: 'Published' },
  { value: 'scheduled', label: 'Scheduled' },
  { value: 'draft', label: 'Drafts' },
];

const day = (iso) => (iso ? new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }) : '');

const statusOf = (post) => {
  if (post.is_scheduled) return { variant: 'pending', label: 'Scheduled', when: `Goes live ${day(post.published_at)}` };
  if (post.status === 'published') return { variant: 'published', label: 'Published', when: day(post.published_at) };
  return { variant: 'draft', label: 'Draft', when: `Edited ${day(post.updated_at)}` };
};

export default function Blog() {
  const navigate = useNavigate();
  const [posts, setPosts] = useState([]);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [status, setStatus] = useState('');
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [deleting, setDeleting] = useState(null);
  const [tag, setTag] = useState('');
  const [tags, setTags] = useState([]);
  const [view, chooseView] = useSavedView('tux_blog_view');
  const [managingTags, setManagingTags] = useState(false);

  const load = useCallback(async () => {
    try {
      setLoading(true);
      const response = await postAPI.list({ status: status || undefined, tag: tag || undefined, search: search.trim() || undefined, page, per_page: 15 });
      setPosts(response.data.data || []);
      setMeta(response.data.meta || { current_page: 1, last_page: 1, total: 0 });
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not load posts'));
    } finally {
      setLoading(false);
    }
  }, [status, tag, search, page]);

  const loadTags = useCallback(() => {
    postTagAPI.list().then((response) => setTags(response.data.data || [])).catch(() => { /* filtering by tag is optional */ });
  }, []);

  useEffect(() => { loadTags(); }, [loadTags]);

  // Typing in the search box waits a moment before asking the server.
  useEffect(() => {
    const timer = setTimeout(load, search ? 250 : 0);
    return () => clearTimeout(timer);
  }, [load, search]);

  const remove = async () => {
    try {
      await postAPI.delete(deleting.id);
      toast.success('Post deleted');
      setDeleting(null);
      await load();
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not delete post'));
    }
  };

  const filtered = Boolean(status || tag || search.trim());

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="page-heading">Blog</h1>
          <p className="page-deck">Write posts, keep them as drafts, then publish them to the blog on your website.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="secondary" onClick={() => setManagingTags(true)}>Tags</Button>
          <Button onClick={() => navigate('/dashboard/blog/create')}><Plus className="h-4 w-4" weight="bold" />New post</Button>
        </div>
      </div>

      <div className="flex flex-wrap items-center gap-x-4 gap-y-3">
        <div className="flex flex-wrap gap-2" role="group" aria-label="Filter posts">
          {FILTERS.map((filter) => (
            <button
              key={filter.value}
              type="button"
              aria-pressed={status === filter.value}
              onClick={() => { setStatus(filter.value); setPage(1); }}
              className={`min-h-8 rounded-full border px-3.5 text-[13px] font-medium ${status === filter.value
                ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-[var(--color-accent-ink)]'
                : 'border-[var(--color-rule-2)] text-[var(--color-ink-2)] hover:bg-[var(--color-paper-2)]'}`}
            >
              {filter.label}
            </button>
          ))}
        </div>
        {tags.length > 0 && (
          <select
            className="field-control min-h-9 w-full sm:ml-auto sm:w-48"
            value={tag}
            onChange={(event) => { setTag(event.target.value); setPage(1); }}
            aria-label="Filter by tag"
          >
            <option value="">All tags</option>
            {tags.map((option) => <option key={option.slug} value={option.slug}>{option.name} ({option.posts_count})</option>)}
          </select>
        )}
        <input
          type="search"
          className={`field-control min-h-9 w-full sm:w-72 ${tags.length > 0 ? '' : 'sm:ml-auto'}`}
          value={search}
          onChange={(event) => { setSearch(event.target.value); setPage(1); }}
          placeholder="Search posts"
          aria-label="Search posts"
        />
        <ViewSwitch view={view} onChange={chooseView} id="blog-view" />
      </div>

      {loading ? <ListSkeleton rows={5} /> : posts.length === 0 ? (
        <Card className="p-12 text-center">
          <IconBlog className="mx-auto h-9 w-9 text-[var(--color-muted)]" />
          <h2 className="mt-3 text-base font-semibold">{filtered ? 'No posts match' : 'No posts yet'}</h2>
          <p className="mt-2 text-sm text-[var(--color-muted)]">
            {filtered ? 'Try another filter or search.' : 'Write your first post. The blog appears on your website once one is published.'}
          </p>
        </Card>
      ) : view === 'gallery' ? (
        <ul className="grid gap-5 [grid-template-columns:repeat(auto-fill,minmax(17rem,1fr))]">
          {posts.map((post) => {
            const state = statusOf(post);
            const edit = `/dashboard/blog/${post.id}/edit`;
            return (
              <li key={post.id} className="work-surface flex flex-col overflow-hidden">
                <Link to={edit} aria-label={`Edit ${post.title}`} tabIndex={-1} className="block">
                  <PostThumb post={post} />
                </Link>
                <div className="flex flex-1 items-start justify-between gap-3 px-4 py-3">
                  <div className="flex min-h-full min-w-0 flex-col">
                    <Link to={edit} className="block truncate font-medium text-[var(--color-ink)] hover:underline">{post.title}</Link>
                    <div className="font-data mt-0.5 truncate text-xs text-[var(--color-muted)]">{post.path}</div>
                    <div className="mt-1.5 truncate text-xs text-[var(--color-ink-2)]">
                      {post.tags?.length ? post.tags.map((item) => item.name).join(', ') : <span className="text-[var(--color-muted)]">No tags</span>}
                    </div>
                    <div className="mt-auto flex items-center gap-2.5 pt-2.5">
                      <Badge variant={state.variant}>{state.label}</Badge>
                      <span className="truncate text-xs text-[var(--color-muted)]">{state.when}</span>
                    </div>
                  </div>
                  <Button variant="iconDanger" size="icon" aria-label={`Delete ${post.title}`} title="Delete" onClick={() => setDeleting(post)}>
                    <Trash className="h-4 w-4" />
                  </Button>
                </div>
              </li>
            );
          })}
        </ul>
      ) : (
        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHeadCell>Post</TableHeadCell>
                <TableHeadCell>Tags</TableHeadCell>
                <TableHeadCell>Status</TableHeadCell>
                <TableHeadCell>Date</TableHeadCell>
                <TableHeadCell className="text-right">Actions</TableHeadCell>
              </TableRow>
            </TableHeader>
            <TableBody>
              {posts.map((post) => {
                const state = statusOf(post);
                return (
                  <TableRow key={post.id}>
                    <TableCell>
                      <button className="text-left font-semibold hover:underline" onClick={() => navigate(`/dashboard/blog/${post.id}/edit`)}>{post.title}</button>
                      <div className="font-data mt-1 text-[var(--color-muted)]">{post.path}</div>
                    </TableCell>
                    <TableCell className="text-[var(--color-ink-2)]">{post.tags?.length ? post.tags.map((item) => item.name).join(', ') : <span className="text-[var(--color-muted)]">None</span>}</TableCell>
                    <TableCell><Badge variant={state.variant}>{state.label}</Badge></TableCell>
                    <TableCell className="whitespace-nowrap text-[var(--color-ink-2)]">{state.when}</TableCell>
                    <TableCell>
                      <div className="flex justify-end gap-1">
                        <Button variant="icon" size="icon" aria-label={`Edit ${post.title}`} title="Edit" onClick={() => navigate(`/dashboard/blog/${post.id}/edit`)}><PencilSimple className="h-4 w-4" /></Button>
                        <Button variant="iconDanger" size="icon" aria-label={`Delete ${post.title}`} title="Delete" onClick={() => setDeleting(post)}><Trash className="h-4 w-4" /></Button>
                      </div>
                    </TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </Card>
      )}

      {meta.last_page > 1 && (
        <div className="flex items-center justify-between gap-4 text-sm text-[var(--color-muted)]">
          <span>Page {meta.current_page} of {meta.last_page}</span>
          <div className="flex gap-2">
            <Button variant="secondary" size="sm" disabled={meta.current_page <= 1} onClick={() => setPage((n) => n - 1)}>Newer</Button>
            <Button variant="secondary" size="sm" disabled={meta.current_page >= meta.last_page} onClick={() => setPage((n) => n + 1)}>Older</Button>
          </div>
        </div>
      )}

      <Modal isOpen={!!deleting} onClose={() => setDeleting(null)} className="w-full max-w-md">
        <ModalHeader onClose={() => setDeleting(null)}><ModalTitle>Delete this post?</ModalTitle></ModalHeader>
        <ModalContent>
          <p className="text-sm text-[var(--color-ink-2)]">
            “{deleting?.title}” will be removed. If it is on your website, it comes off the next time the site is published.
          </p>
        </ModalContent>
        <ModalFooter>
          <Button variant="secondary" onClick={() => setDeleting(null)}>Cancel</Button>
          <Button variant="danger" onClick={remove}>Delete post</Button>
        </ModalFooter>
      </Modal>

      <TagManager
        isOpen={managingTags}
        onClose={() => setManagingTags(false)}
        onChanged={() => {
          loadTags();
          // A tag being filtered on may have gone or changed its address.
          setTag('');
          load();
        }}
      />
    </div>
  );
}

/**
 * A post's picture for the gallery: its cover, or, without one, its title set
 * large in the same place, so every card in a row keeps the same shape.
 */
function PostThumb({ post }) {
  return (
    <div className="relative aspect-[16/10] overflow-hidden bg-[var(--color-paper-3)]">
      {post.cover_image ? (
        <img src={post.cover_image} alt="" loading="lazy" decoding="async" className="absolute inset-0 h-full w-full object-cover" />
      ) : (
        <div className="absolute inset-0 grid place-items-center p-5 text-center">
          <div className="line-clamp-3 text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink-2)]">{post.title}</div>
        </div>
      )}
    </div>
  );
}

import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { IconBlog, PencilSimple, Plus, Trash } from '../lib/icons';
import { postAPI, postTagAPI, apiErrorMessage } from '../lib/api';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import { ListSkeleton } from '../components/ui/Skeleton';
import Modal, { ModalContent, ModalFooter, ModalHeader, ModalTitle } from '../components/ui/Modal';
import { Card } from '../components/ui/Card';
import { Table, TableBody, TableCell, TableHeadCell, TableHeader, TableRow } from '../components/ui/Table';

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

  useEffect(() => {
    postTagAPI.list().then((response) => setTags(response.data.data || [])).catch(() => { /* filtering by tag is optional */ });
  }, []);

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
        <Button onClick={() => navigate('/dashboard/blog/create')}><Plus className="h-4 w-4" weight="bold" />New post</Button>
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
      </div>

      {loading ? <ListSkeleton rows={5} /> : posts.length === 0 ? (
        <Card className="p-12 text-center">
          <IconBlog className="mx-auto h-9 w-9 text-[var(--color-muted)]" />
          <h2 className="mt-3 text-base font-semibold">{filtered ? 'No posts match' : 'No posts yet'}</h2>
          <p className="mt-2 text-sm text-[var(--color-muted)]">
            {filtered ? 'Try another filter or search.' : 'Write your first post. The blog appears on your website once one is published.'}
          </p>
        </Card>
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
    </div>
  );
}

import { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { IconBlog, PencilSimple, Plus, Trash } from '../lib/icons';
import { postAPI, postCategoryAPI, apiErrorMessage } from '../lib/api';
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
  const [managing, setManaging] = useState(false);

  const load = useCallback(async () => {
    try {
      setLoading(true);
      const response = await postAPI.list({ status: status || undefined, search: search.trim() || undefined, page, per_page: 15 });
      setPosts(response.data.data || []);
      setMeta(response.data.meta || { current_page: 1, last_page: 1, total: 0 });
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not load posts'));
    } finally {
      setLoading(false);
    }
  }, [status, search, page]);

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

  const filtered = Boolean(status || search.trim());

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 className="page-heading">Blog</h1>
          <p className="page-deck">Write posts, keep them as drafts, then publish them to the blog on your website.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="secondary" onClick={() => setManaging(true)}>Categories</Button>
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
        <input
          type="search"
          className="field-control min-h-9 w-full sm:ml-auto sm:w-72"
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
                <TableHeadCell>Category</TableHeadCell>
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
                    <TableCell className="text-[var(--color-ink-2)]">{post.category?.name || <span className="text-[var(--color-muted)]">None</span>}</TableCell>
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

      <CategoryManager isOpen={managing} onClose={() => { setManaging(false); load(); }} />
    </div>
  );
}

/** Add, rename and remove the categories posts are filed under. */
export function CategoryManager({ isOpen, onClose }) {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(false);
  const [name, setName] = useState('');
  const [renaming, setRenaming] = useState(null);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    try {
      setLoading(true);
      setCategories((await postCategoryAPI.list()).data.data || []);
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not load categories'));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (isOpen) { setName(''); setRenaming(null); load(); }
  }, [isOpen, load]);

  const attempt = async (action, failure) => {
    setBusy(true);
    try { await action(); await load(); return true; }
    catch (error) { toast.error(apiErrorMessage(error, failure)); return false; }
    finally { setBusy(false); }
  };

  const add = async (event) => {
    event.preventDefault();
    if (!name.trim()) return;
    if (await attempt(() => postCategoryAPI.create({ name: name.trim() }), 'Could not add the category')) setName('');
  };

  const rename = async (event) => {
    event.preventDefault();
    if (!renaming.name.trim()) return;
    if (await attempt(() => postCategoryAPI.update(renaming.id, { name: renaming.name.trim() }), 'Could not rename the category')) setRenaming(null);
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-lg">
      <ModalHeader onClose={onClose}>
        <div>
          <ModalTitle>Categories</ModalTitle>
          <p className="mt-1 text-sm text-[var(--color-muted)]">Group posts by subject. A category shows on the blog once it has a published post.</p>
        </div>
      </ModalHeader>
      <ModalContent className="space-y-5">
        <form onSubmit={add} className="flex items-end gap-2">
          <label className="min-w-0 flex-1 text-sm font-medium text-[var(--color-ink-2)]">
            New category
            <input className="field-control mt-1.5" value={name} onChange={(event) => setName(event.target.value)} placeholder="Company news" maxLength={100} />
          </label>
          <Button type="submit" disabled={busy || !name.trim()}>Add</Button>
        </form>

        {loading && categories.length === 0 ? <ListSkeleton rows={3} /> : categories.length === 0 ? (
          <p className="text-sm text-[var(--color-muted)]">No categories yet.</p>
        ) : (
          <ul className="divide-y divide-[var(--color-rule-2)] rounded-[var(--radius-m)] border border-[var(--color-rule-2)]">
            {categories.map((category) => (
              <li key={category.id} className="flex min-h-12 items-center gap-2 px-3 py-1.5">
                {renaming?.id === category.id ? (
                  <form onSubmit={rename} className="flex flex-1 items-center gap-2">
                    <input
                      className="field-control min-h-9 flex-1"
                      value={renaming.name}
                      autoFocus
                      aria-label={`New name for ${category.name}`}
                      onChange={(event) => setRenaming({ ...renaming, name: event.target.value })}
                      onKeyDown={(event) => { if (event.key === 'Escape') setRenaming(null); }}
                    />
                    <Button type="submit" size="sm" disabled={busy}>Save</Button>
                    <Button type="button" size="sm" variant="secondary" onClick={() => setRenaming(null)}>Cancel</Button>
                  </form>
                ) : (
                  <>
                    <span className="min-w-0 flex-1 truncate text-sm font-medium">{category.name}</span>
                    <span className="shrink-0 text-[13px] text-[var(--color-muted)]">{category.posts_count} {category.posts_count === 1 ? 'post' : 'posts'}</span>
                    <Button variant="icon" size="icon" aria-label={`Rename ${category.name}`} title="Rename" onClick={() => setRenaming({ id: category.id, name: category.name })}><PencilSimple className="h-4 w-4" /></Button>
                    <Button
                      variant="iconDanger" size="icon" aria-label={`Delete ${category.name}`} title="Delete" disabled={busy}
                      onClick={() => {
                        if (category.posts_count > 0 && !window.confirm(`Delete “${category.name}”? Its ${category.posts_count === 1 ? 'post stays' : `${category.posts_count} posts stay`}, without a category.`)) return;
                        attempt(() => postCategoryAPI.delete(category.id), 'Could not delete the category');
                      }}
                    ><Trash className="h-4 w-4" /></Button>
                  </>
                )}
              </li>
            ))}
          </ul>
        )}
      </ModalContent>
      <ModalFooter><Button variant="secondary" onClick={onClose}>Done</Button></ModalFooter>
    </Modal>
  );
}

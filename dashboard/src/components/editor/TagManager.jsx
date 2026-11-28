import { useEffect, useState } from 'react';
import toast from 'react-hot-toast';
import { PencilSimple, Trash } from '../../lib/icons';
import { postTagAPI, apiErrorMessage } from '../../lib/api';
import Button from '../ui/Button';
import Modal, { ModalContent, ModalFooter, ModalHeader, ModalTitle } from '../ui/Modal';
import { ListSkeleton } from '../ui/Skeleton';

/**
 * The blog's tags, to rename or delete. Renaming one to the name of another
 * merges the two; deleting one takes it off its posts, which stay.
 * `onChanged` is told after every change, so the list behind can refresh.
 */
export default function TagManager({ isOpen, onClose, onChanged }) {
  const [tags, setTags] = useState([]);
  const [loading, setLoading] = useState(true);
  const [renaming, setRenaming] = useState(null);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!isOpen) return undefined;
    let active = true;
    postTagAPI.list()
      .then((response) => { if (active) setTags(response.data.data || []); })
      .catch((error) => toast.error(apiErrorMessage(error, 'Could not load the tags')))
      .finally(() => { if (active) setLoading(false); });
    return () => { active = false; };
  }, [isOpen]);

  const attempt = async (work, failure) => {
    setBusy(true);
    try {
      await work();
      setTags((await postTagAPI.list()).data.data || []);
      onChanged?.();
      return true;
    } catch (error) {
      toast.error(apiErrorMessage(error, failure));
      return false;
    } finally {
      setBusy(false);
    }
  };

  const rename = async (event) => {
    event.preventDefault();
    const name = renaming.name.trim().replace(/\s+/g, ' ');
    const tag = tags.find((item) => item.slug === renaming.slug);
    if (!name || name === tag?.name) { setRenaming(null); return; }

    const other = tags.find((item) => item.slug !== renaming.slug && item.name.toLowerCase() === name.toLowerCase());
    if (other && !window.confirm(`“${other.name}” already exists. Move the posts tagged “${tag.name}” to it?`)) return;

    if (await attempt(() => postTagAPI.rename(renaming.slug, name), 'Could not rename the tag')) {
      setRenaming(null);
      toast.success(other ? `Merged into “${other.name}”` : 'Tag renamed');
    }
  };

  const remove = async (tag) => {
    const posts = tag.posts_count === 1 ? 'Its post stays' : `Its ${tag.posts_count} posts stay`;
    if (!window.confirm(`Delete the tag “${tag.name}”? ${posts}, without it.`)) return;
    if (await attempt(() => postTagAPI.delete(tag.slug), 'Could not delete the tag')) toast.success('Tag deleted');
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-lg" aria-label="Tags">
      <ModalHeader onClose={onClose}>
        <div>
          <ModalTitle>Tags</ModalTitle>
          <p className="mt-1 text-sm text-[var(--color-muted)]">Rename a tag to fix it everywhere. Give it the name of another tag to merge the two.</p>
        </div>
      </ModalHeader>
      <ModalContent>
        {loading ? <ListSkeleton rows={4} /> : tags.length === 0 ? (
          <p className="py-6 text-center text-sm text-[var(--color-muted)]">No tags yet. Add them to a post as you write it.</p>
        ) : (
          <ul className="divide-y divide-[var(--color-rule-2)]" aria-label="Tags in use">
            {tags.map((tag) => (
              <li key={tag.slug} className="flex min-h-14 items-center gap-2 py-2">
                {renaming?.slug === tag.slug ? (
                  <form onSubmit={rename} className="flex flex-1 items-center gap-2">
                    <input
                      autoFocus
                      value={renaming.name}
                      maxLength={50}
                      onChange={(event) => setRenaming({ ...renaming, name: event.target.value })}
                      onKeyDown={(event) => { if (event.key === 'Escape') { event.stopPropagation(); setRenaming(null); } }}
                      aria-label={`New name for ${tag.name}`}
                      className="field-control min-h-9 flex-1"
                    />
                    <Button type="submit" size="sm" disabled={busy}>Save</Button>
                    <Button type="button" size="sm" variant="secondary" onClick={() => setRenaming(null)}>Cancel</Button>
                  </form>
                ) : (
                  <>
                    <span className="min-w-0 flex-1 truncate text-sm font-medium text-[var(--color-ink)]">{tag.name}</span>
                    <span className="shrink-0 text-[13px] text-[var(--color-muted)]">{tag.posts_count} {tag.posts_count === 1 ? 'post' : 'posts'}</span>
                    <Button variant="icon" size="icon" aria-label={`Rename ${tag.name}`} title="Rename" disabled={busy} onClick={() => setRenaming({ slug: tag.slug, name: tag.name })}>
                      <PencilSimple className="h-4 w-4" />
                    </Button>
                    <Button variant="iconDanger" size="icon" aria-label={`Delete ${tag.name}`} title="Delete" disabled={busy} onClick={() => remove(tag)}>
                      <Trash className="h-4 w-4" />
                    </Button>
                  </>
                )}
              </li>
            ))}
          </ul>
        )}
      </ModalContent>
      <ModalFooter><Button type="button" variant="secondary" onClick={onClose}>Done</Button></ModalFooter>
    </Modal>
  );
}

import { useEffect, useState } from 'react';
import { ArrowCounterClockwise, ClockCounterClockwise } from '../../lib/icons';
import toast from 'react-hot-toast';
import { pageAPI, postAPI } from '../../lib/api';
import Button from '../ui/Button';
import Modal, { ModalContent, ModalFooter, ModalHeader, ModalTitle } from '../ui/Modal';
import Spinner from '../ui/Spinner';

const KINDS = {
  page: { load: (id) => pageAPI.revisions(id), title: 'Page history', untitled: 'Untitled page' },
  post: { load: (id) => postAPI.revisions(id), title: 'Post history', untitled: 'Untitled post' },
};

/** The earlier saved states of a page or a post (`kind`), to restore one. */
export default function RevisionHistory({ isOpen, kind = 'page', pageId, onClose, onRestore }) {
  const subject = KINDS[kind];
  const [revisions, setRevisions] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (!isOpen || !pageId) return;
    let active = true;
    subject.load(pageId)
      .then((response) => { if (active) setRevisions(response.data.data || []); })
      .catch(() => toast.error(`Could not load the ${kind} history`))
      .finally(() => { if (active) setLoading(false); });
    return () => { active = false; };
    // subject follows kind.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen, kind, pageId]);

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-2xl" aria-label={subject.title}>
      <ModalHeader onClose={onClose}>
        <div>
          <ModalTitle>{subject.title}</ModalTitle>
          <p className="mt-1 text-sm text-[var(--color-muted)]">Restore a previous saved state, then publish when it looks right.</p>
        </div>
      </ModalHeader>
      <ModalContent>
        {loading ? (
          <div className="flex min-h-48 items-center justify-center"><Spinner /></div>
        ) : revisions.length ? (
          <ol className="divide-y divide-[var(--color-rule-2)]">
            {revisions.map((revision) => (
              <li key={revision.id} className="flex items-center gap-4 py-4">
                <ClockCounterClockwise className="h-5 w-5 shrink-0 text-[var(--color-muted)]" />
                <div className="min-w-0 flex-1">
                  <p className="truncate text-sm font-semibold text-[var(--color-ink)]">{revision.snapshot?.title || subject.untitled}</p>
                  <p className="mt-0.5 text-xs text-[var(--color-muted)]">
                    {new Date(revision.created_at).toLocaleString()} {revision.author ? `by ${revision.author}` : ''}
                  </p>
                </div>
                <Button type="button" size="sm" variant="secondary" onClick={() => onRestore(revision.snapshot)}>
                  <ArrowCounterClockwise className="h-4 w-4" /> Restore
                </Button>
              </li>
            ))}
          </ol>
        ) : (
          <div className="py-14 text-center">
            <ClockCounterClockwise className="mx-auto h-7 w-7 text-[var(--color-muted)]" />
            <p className="mt-3 font-medium text-[var(--color-ink)]">No earlier versions yet</p>
            <p className="mt-1 text-sm text-[var(--color-muted)]">{`History appears after this ${kind} has been changed and saved.`}</p>
          </div>
        )}
      </ModalContent>
      <ModalFooter><Button type="button" variant="secondary" onClick={onClose}>Close</Button></ModalFooter>
    </Modal>
  );
}

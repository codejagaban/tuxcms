import { useState, useEffect } from 'react';
import { Plus, PencilSimple, Trash, FileText, List, SquaresFour } from '../../lib/icons';
import { Link } from 'react-router-dom';
import { motion as Motion } from 'motion/react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Badge from '../../components/ui/Badge';
import { ListSkeleton } from '../../components/ui/Skeleton';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { Table, TableHeader, TableBody, TableRow, TableCell, TableHeadCell, Pagination } from '../../components/ui/Table';
import { pageAPI } from '../../lib/api';
import PageThumb from '../../components/PageThumb';

// Remembered between visits; a private window may refuse, and then it is
// simply the list each time.
const VIEW_KEY = 'tux_pages_view';
const savedView = () => {
  try {
    return localStorage.getItem(VIEW_KEY) === 'gallery' ? 'gallery' : 'list';
  } catch {
    return 'list';
  }
};

const Pages = () => {
  const [pages, setPages] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [deleteModal, setDeleteModal] = useState({ isOpen: false, pageId: null, pageTitle: '' });
  const [isDeleting, setIsDeleting] = useState(false);
  const [view, setView] = useState(savedView);

  const chooseView = (next) => {
    setView(next);
    try {
      localStorage.setItem(VIEW_KEY, next);
    } catch {
      // Nothing to do: the choice lasts for this visit.
    }
  };

  const pathOf = (page) => (page.is_homepage ? '/' : page.path || `/${page.slug}`);
  const statusOf = (page) => (page.is_scheduled
    ? <Badge variant="pending">Scheduled</Badge>
    : <Badge variant={getStatusBadge(page.status)}>{page.status || 'draft'}</Badge>);

  const itemsPerPage = 10;

  useEffect(() => {
      fetchPages();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentPage, searchTerm]);

  const fetchPages = async () => {
    try {
      setIsLoading(true);
      const response = await pageAPI.list({
        page: currentPage,
        per_page: itemsPerPage,
        search: searchTerm,
      });

      const data = response.data.data || [];
      setPages(data);

      const total = response.data.meta?.total || data.length;
      setTotalPages(Math.ceil(total / itemsPerPage));
    } catch (error) {
      console.error('Error fetching pages:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load pages');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleDeleteClick = (page) => {
    setDeleteModal({
      isOpen: true,
      pageId: page.id,
      pageTitle: page.title,
    });
  };

  const handleConfirmDelete = async () => {
    try {
      setIsDeleting(true);
      await pageAPI.delete(deleteModal.pageId);
      toast.success('Page deleted successfully');
      setDeleteModal({ isOpen: false, pageId: null, pageTitle: '' });
      setCurrentPage(1);
      fetchPages();
    } catch (error) {
      console.error('Error deleting page:', error);
      toast.error('Failed to delete page');
    } finally {
      setIsDeleting(false);
    }
  };

  const handleCloseDeleteModal = () => {
    setDeleteModal({ isOpen: false, pageId: null, pageTitle: '' });
  };

  const getStatusBadge = (status) => {
    const statusMap = {
      published: 'published',
      draft: 'draft',
      archived: 'danger',
    };
    return statusMap[status] || 'default';
  };

  // A date alone hides same-day edits, which makes it look like a save didn't
  // land. Show recency for anything from the last day.
  const formatDate = (dateString) => {
    if (!dateString) return '—';

    const date = new Date(dateString);
    const seconds = Math.floor((Date.now() - date.getTime()) / 1000);

    if (seconds < 60) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hr ago`;

    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="page-heading">Pages</h1>
          <p className="page-deck">Find, edit, and check the publishing state of every page.</p>
        </div>
        <Link to="/dashboard/pages/create">
          <Button variant="primary">
            <Plus className="h-4 w-4" weight="bold" />
            New page
          </Button>
        </Link>
      </div>

      {/* Search, and how to look at the results */}
      <div className="flex items-center gap-3">
        <Input
          type="text"
          aria-label="Search pages"
          placeholder="Search by title or path"
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setCurrentPage(1);
          }}
          containerClassName="w-full"
        />
        <div role="radiogroup" aria-label="View" className="flex shrink-0 gap-0.5 rounded-full bg-[var(--color-paper-3)] p-0.5">
          {[['list', 'List', List], ['gallery', 'Gallery', SquaresFour]].map(([value, label, Icon]) => (
            <button
              key={value}
              type="button"
              role="radio"
              aria-checked={view === value}
              aria-label={`${label} view`}
              title={`${label} view`}
              onClick={() => chooseView(value)}
              className={`relative grid h-10 w-11 place-items-center rounded-full transition-colors duration-200 ${
                view === value ? 'text-[var(--color-ink)]' : 'text-[var(--color-muted)] hover:text-[var(--color-ink)]'
              }`}
            >
              {view === value && (
                <Motion.span
                  layoutId="pages-view"
                  className="absolute inset-0 rounded-full bg-[var(--color-paper)] shadow-[var(--glass-chip-edge)]"
                  transition={{ type: 'spring', stiffness: 460, damping: 40 }}
                />
              )}
              <Icon className="relative h-[18px] w-[18px]" />
            </button>
          ))}
        </div>
      </div>

      {/* Loading State */}
      {isLoading && (
        <ListSkeleton rows={7} />
      )}

      {/* Empty State */}
      {!isLoading && pages.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-[var(--color-muted)] mb-4">
                <FileText className="mx-auto h-9 w-9" />
              </div>
              <h3 className="text-lg font-medium text-[var(--color-ink)]">
                {searchTerm ? 'No pages found' : 'No pages yet'}
              </h3>
              <p className="text-[var(--color-muted)] mt-2">
                {searchTerm ? 'Try adjusting your search' : 'Create your first page to get started'}
              </p>
              {!searchTerm && (
                <Link to="/dashboard/pages/create" className="mt-4 inline-block">
                  <Button variant="primary">Create Page</Button>
                </Link>
              )}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Gallery: each page as a small picture of itself */}
      {!isLoading && pages.length > 0 && view === 'gallery' && (
        <>
          <ul className="grid gap-5 [grid-template-columns:repeat(auto-fill,minmax(17rem,1fr))]">
            {pages.map((page) => (
              <li key={page.id} className="work-surface group overflow-hidden">
                <Link to={`/dashboard/pages/${page.id}/edit`} aria-label={`Edit ${page.title}`} className="block">
                  <PageThumb page={page} />
                </Link>
                <div className="flex items-start justify-between gap-3 px-4 py-3">
                  <div className="min-w-0">
                    <Link to={`/dashboard/pages/${page.id}/edit`} className="block truncate font-medium text-[var(--color-ink)] hover:underline">
                      {page.title}
                    </Link>
                    <div className="font-data mt-0.5 truncate text-xs text-[var(--color-muted)]">{pathOf(page)}</div>
                    <div className="mt-2.5 flex items-center gap-2.5">
                      {statusOf(page)}
                      <span className="text-xs text-[var(--color-muted)]">{formatDate(page.updated_at)}</span>
                    </div>
                  </div>
                  <Button
                    variant="iconDanger"
                    size="icon"
                    aria-label={`Delete ${page.title}`}
                    title="Delete"
                    onClick={() => handleDeleteClick(page)}
                  >
                    <Trash className="h-4 w-4" weight="bold" />
                  </Button>
                </div>
              </li>
            ))}
          </ul>
          {totalPages > 1 && (
            <Card>
              <Pagination currentPage={currentPage} totalPages={totalPages} onPageChange={setCurrentPage} />
            </Card>
          )}
        </>
      )}

      {/* Pages Table */}
      {!isLoading && pages.length > 0 && view === 'list' && (
        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHeadCell>Title</TableHeadCell>
                <TableHeadCell>Path</TableHeadCell>
                <TableHeadCell>Template</TableHeadCell>
                <TableHeadCell>Status</TableHeadCell>
                <TableHeadCell>Author</TableHeadCell>
                <TableHeadCell>Updated</TableHeadCell>
                <TableHeadCell className="text-right">Actions</TableHeadCell>
              </TableRow>
            </TableHeader>
            <TableBody>
              {pages.map((page) => (
                <TableRow key={page.id}>
                  <TableCell className="font-medium">
                    <Link
                      to={`/dashboard/pages/${page.id}/edit`}
                      className="text-[var(--color-ink)] hover:underline"
                    >
                      {page.title}
                    </Link>
                  </TableCell>
                  <TableCell className="font-data text-[var(--color-muted)]">{pathOf(page)}</TableCell>
                  <TableCell className="text-[var(--color-muted)] capitalize">{page.template || 'default'}</TableCell>
                  <TableCell>{statusOf(page)}</TableCell>
                  <TableCell className="text-[var(--color-muted)]">{page.author?.name || '—'}</TableCell>
                  <TableCell className="text-[var(--color-muted)]">{formatDate(page.updated_at)}</TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-1">
                      <Link to={`/dashboard/pages/${page.id}/edit`}>
                        <Button variant="icon" size="icon" aria-label={`Edit ${page.title}`} title="Edit">
                          <PencilSimple className="h-4 w-4" weight="bold" />
                        </Button>
                      </Link>
                      <Button
                        variant="iconDanger"
                        size="icon"
                        aria-label={`Delete ${page.title}`}
                        title="Delete"
                        onClick={() => handleDeleteClick(page)}
                      >
                        <Trash className="h-4 w-4" weight="bold" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>

          {totalPages > 1 && (
            <Pagination
              currentPage={currentPage}
              totalPages={totalPages}
              onPageChange={setCurrentPage}
            />
          )}
        </Card>
      )}

      {/* Delete Confirmation Modal */}
      <Modal
        isOpen={deleteModal.isOpen}
        onClose={handleCloseDeleteModal}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={handleCloseDeleteModal}>
          <ModalTitle>Delete Page</ModalTitle>
        </ModalHeader>
        <ModalContent>
          <p className="text-[var(--color-muted)]">
            Are you sure you want to delete <strong>{deleteModal.pageTitle}</strong>? This action cannot be undone.
          </p>
        </ModalContent>
        <ModalFooter>
          <Button
            variant="secondary"
            onClick={handleCloseDeleteModal}
            disabled={isDeleting}
          >
            Cancel
          </Button>
          <Button
            variant="danger"
            onClick={handleConfirmDelete}
            isLoading={isDeleting}
          >
            Delete
          </Button>
        </ModalFooter>
      </Modal>
    </div>
  );
};

export default Pages;

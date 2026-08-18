import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Search } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Badge from '../../components/ui/Badge';
import Spinner from '../../components/ui/Spinner';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { Table, TableHeader, TableBody, TableRow, TableCell, TableHeadCell, Pagination } from '../../components/ui/Table';
import { pageAPI } from '../../lib/api';

const Pages = () => {
  const navigate = useNavigate();
  const [pages, setPages] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [deleteModal, setDeleteModal] = useState({ isOpen: false, pageId: null, pageTitle: '' });
  const [isDeleting, setIsDeleting] = useState(false);

  const itemsPerPage = 10;

  useEffect(() => {
      fetchPages();
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

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Pages</h1>
          <p className="text-gray-600 mt-1">Manage your website pages</p>
        </div>
        <Link to="/dashboard/pages/create">
          <Button variant="primary">
            <Plus className="h-4 w-4" />
            New Page
          </Button>
        </Link>
      </div>

      {/* Search Bar */}
      <Input
        type="text"
        placeholder="Search pages by title or slug..."
        value={searchTerm}
        onChange={(e) => {
          setSearchTerm(e.target.value);
          setCurrentPage(1);
        }}
        containerClassName="w-full"
      />

      {/* Loading State */}
      {isLoading && (
        <div className="flex items-center justify-center py-20">
          <Spinner size="lg" />
        </div>
      )}

      {/* Empty State */}
      {!isLoading && pages.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  📄
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">
                {searchTerm ? 'No pages found' : 'No pages yet'}
              </h3>
              <p className="text-gray-600 mt-2">
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

      {/* Pages Table */}
      {!isLoading && pages.length > 0 && (
        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHeadCell>Title</TableHeadCell>
                <TableHeadCell>Slug</TableHeadCell>
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
                      className="text-gray-900 hover:text-blue-600 hover:underline"
                    >
                      {page.title}
                    </Link>
                  </TableCell>
                  <TableCell className="text-gray-600">/{page.slug}</TableCell>
                  <TableCell className="text-gray-600 capitalize">{page.template || 'default'}</TableCell>
                  <TableCell>
                    <Badge variant={getStatusBadge(page.status)}>
                      {page.status || 'draft'}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-gray-600">{page.author_name || 'Unknown'}</TableCell>
                  <TableCell className="text-gray-600">{formatDate(page.updated_at)}</TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-2">
                      <Link to={`/dashboard/pages/${page.id}/edit`}>
                        <Button variant="ghost" size="sm">
                          <Edit2 className="h-4 w-4" />
                        </Button>
                      </Link>
                      <Button
                        variant="danger"
                        size="sm"
                        onClick={() => handleDeleteClick(page)}
                      >
                        <Trash2 className="h-4 w-4" />
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
          <p className="text-gray-600">
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

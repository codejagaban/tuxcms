import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Eye, Trash2, ArrowLeft } from 'lucide-react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Badge from '../../components/ui/Badge';
import Spinner from '../../components/ui/Spinner';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { Table, TableHeader, TableBody, TableRow, TableCell, TableHeadCell, Pagination } from '../../components/ui/Table';
import { formAPI } from '../../lib/api';
import { useAuth } from '../../lib/auth';

const FormSubmissions = () => {
  const { currentTenant, isSuperAdmin } = useAuth();
  const { id: formId } = useParams();
  const navigate = useNavigate();
  const [submissions, setSubmissions] = useState([]);
  const [form, setForm] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [filter, setFilter] = useState('all');
  const [deleteModal, setDeleteModal] = useState({ isOpen: false, submissionId: null });
  const [isDeleting, setIsDeleting] = useState(false);
  const [viewModal, setViewModal] = useState({ isOpen: false, submission: null });

  const itemsPerPage = 10;

  useEffect(() => {
    if (currentTenant || isSuperAdmin) {
      fetchData();
    } else {
      setIsLoading(false);
    }
  }, [currentPage, filter, currentTenant, isSuperAdmin]);

  const fetchData = async () => {
    try {
      setIsLoading(true);

      const response = await formAPI.getSubmissions(formId, {
        page: currentPage,
        per_page: itemsPerPage,
        read: filter === 'unread' ? false : undefined,
      });

      const data = response.data.data || [];
      setSubmissions(data);

      const total = response.data.meta?.total || data.length;
      setTotalPages(Math.ceil(total / itemsPerPage));

      if (!form) {
        const formResponse = await formAPI.get(formId);
        setForm(formResponse.data.data);
      }
    } catch (error) {
      console.error('Error fetching submissions:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load submissions');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleDeleteClick = (submissionId) => {
    setDeleteModal({
      isOpen: true,
      submissionId,
    });
  };

  const handleConfirmDelete = async () => {
    try {
      setIsDeleting(true);
      await formAPI.deleteSubmission(deleteModal.submissionId);
      toast.success('Submission deleted successfully');
      setDeleteModal({ isOpen: false, submissionId: null });
      fetchData();
    } catch (error) {
      console.error('Error deleting submission:', error);
      toast.error('Failed to delete submission');
    } finally {
      setIsDeleting(false);
    }
  };

  const handleCloseDeleteModal = () => {
    setDeleteModal({ isOpen: false, submissionId: null });
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const unreadCount = submissions.filter((s) => !s.is_read).length;

  return (
    <div className="space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="sm" onClick={() => navigate('/dashboard/forms')}>
          <ArrowLeft className="h-4 w-4" />
          Back
        </Button>
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Form Submissions</h1>
          <p className="text-gray-600 mt-1">
            {form && `Submissions for ${form.name}`}
          </p>
        </div>
      </div>

      {isLoading && (
        <div className="flex items-center justify-center py-20">
          <Spinner size="lg" />
        </div>
      )}

      {!isLoading && submissions.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  📊
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">
                {filter === 'unread' ? 'No unread submissions' : 'No submissions yet'}
              </h3>
              <p className="text-gray-600 mt-2">
                {filter === 'unread' ? 'All submissions have been read' : 'Form submissions will appear here'}
              </p>
            </div>
          </CardContent>
        </Card>
      )}

      {!isLoading && submissions.length > 0 && (
        <>
          <div className="flex items-center gap-2">
            <Button
              variant={filter === 'all' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => {
                setFilter('all');
                setCurrentPage(1);
              }}
            >
              All
            </Button>
            <Button
              variant={filter === 'unread' ? 'primary' : 'ghost'}
              size="sm"
              onClick={() => {
                setFilter('unread');
                setCurrentPage(1);
              }}
            >
              Unread {unreadCount > 0 && `(${unreadCount})`}
            </Button>
          </div>

          <Card>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHeadCell>Status</TableHeadCell>
                  <TableHeadCell>Date</TableHeadCell>
                  <TableHeadCell>IP Address</TableHeadCell>
                  <TableHeadCell className="text-right">Actions</TableHeadCell>
                </TableRow>
              </TableHeader>
              <TableBody>
                {submissions.map((submission) => (
                  <TableRow key={submission.id}>
                    <TableCell>
                      <Badge variant={submission.is_read ? 'default' : 'warning'}>
                        {submission.is_read ? 'Read' : 'Unread'}
                      </Badge>
                    </TableCell>
                    <TableCell className="text-gray-600">
                      {formatDate(submission.created_at)}
                    </TableCell>
                    <TableCell className="text-gray-600 font-mono text-sm">
                      {submission.ip_address || 'N/A'}
                    </TableCell>
                    <TableCell>
                      <div className="flex items-center justify-end gap-2">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => setViewModal({ isOpen: true, submission })}
                        >
                          <Eye className="h-4 w-4" />
                        </Button>
                        <Button
                          variant="danger"
                          size="sm"
                          onClick={() => handleDeleteClick(submission.id)}
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
        </>
      )}

      <SubmissionViewModal
        isOpen={viewModal.isOpen}
        submission={viewModal.submission}
        formFields={form?.fields}
        onClose={() => setViewModal({ isOpen: false, submission: null })}
        onSubmissionUpdate={fetchData}
      />

      <Modal
        isOpen={deleteModal.isOpen}
        onClose={handleCloseDeleteModal}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={handleCloseDeleteModal}>
          <ModalTitle>Delete Submission</ModalTitle>
        </ModalHeader>
        <ModalContent>
          <p className="text-gray-600">
            Are you sure you want to delete this submission? This action cannot be undone.
          </p>
        </ModalContent>
        <ModalFooter>
          <Button variant="secondary" onClick={handleCloseDeleteModal} disabled={isDeleting}>
            Cancel
          </Button>
          <Button variant="danger" onClick={handleConfirmDelete} isLoading={isDeleting}>
            Delete
          </Button>
        </ModalFooter>
      </Modal>
    </div>
  );
};

const SubmissionViewModal = ({ isOpen, submission, formFields, onClose, onSubmissionUpdate }) => {
  if (!isOpen || !submission) return null;

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-2xl">
      <ModalHeader onClose={onClose}>
        <ModalTitle>View Submission</ModalTitle>
      </ModalHeader>

      <ModalContent className="space-y-4">
        <div className="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
          <div>
            <p className="text-xs font-medium text-gray-600 uppercase">Submitted</p>
            <p className="text-sm text-gray-900 mt-1">{formatDate(submission.created_at)}</p>
          </div>
          <div>
            <p className="text-xs font-medium text-gray-600 uppercase">IP Address</p>
            <p className="text-sm text-gray-900 mt-1">{submission.ip_address || 'N/A'}</p>
          </div>
        </div>

        <div className="border-t pt-4">
          <h3 className="font-semibold text-gray-900 mb-4">Submission Data</h3>

          {submission.data && Object.entries(submission.data).length > 0 ? (
            <div className="space-y-3">
              {Object.entries(submission.data).map(([key, value]) => {
                const field = formFields?.find((f) => f.name === key);
                const label = field?.label || key;

                return (
                  <div key={key} className="border-l-4 border-blue-200 pl-4">
                    <p className="text-xs font-medium text-gray-600 uppercase">{label}</p>
                    <p className="text-sm text-gray-900 mt-1 break-words">
                      {Array.isArray(value) ? value.join(', ') : String(value)}
                    </p>
                  </div>
                );
              })}
            </div>
          ) : (
            <p className="text-gray-600 text-sm">No data submitted</p>
          )}
        </div>
      </ModalContent>

      <ModalFooter>
        <Button variant="secondary" onClick={onClose}>
          Close
        </Button>
      </ModalFooter>
    </Modal>
  );
};

export default FormSubmissions;

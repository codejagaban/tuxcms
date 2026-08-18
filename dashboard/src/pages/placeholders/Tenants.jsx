import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Search } from 'lucide-react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Badge from '../../components/ui/Badge';
import Spinner from '../../components/ui/Spinner';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { Table, TableHeader, TableBody, TableRow, TableCell, TableHeadCell, Pagination } from '../../components/ui/Table';
import { useAuth } from '../../lib/auth';
import { tenantAPI } from '../../lib/api';

const Tenants = () => {
  const { isSuperAdmin } = useAuth();
  const [tenants, setTenants] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [deleteModal, setDeleteModal] = useState({ isOpen: false, tenantId: null, tenantName: '' });
  const [isDeleting, setIsDeleting] = useState(false);
  const [tenantModal, setTenantModal] = useState({ isOpen: false, editingId: null });

  const itemsPerPage = 10;

  useEffect(() => {
    if (isSuperAdmin) {
      fetchTenants();
    }
  }, [currentPage, searchTerm, isSuperAdmin]);

  if (!isSuperAdmin) {
    return (
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Tenants</h1>
          <p className="text-gray-600 mt-1">Manage system tenants</p>
        </div>

        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  🚫
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">Access Denied</h3>
              <p className="text-gray-600 mt-2">
                You don't have permission to access tenant management
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    );
  }

  const fetchTenants = async () => {
    try {
      setIsLoading(true);
      const response = await tenantAPI.list({
        page: currentPage,
        per_page: itemsPerPage,
        search: searchTerm,
      });

      const data = response.data.data || [];
      setTenants(data);

      const total = response.data.meta?.total || data.length;
      setTotalPages(Math.ceil(total / itemsPerPage));
    } catch (error) {
      console.error('Error fetching tenants:', error);
      toast.error('Failed to load tenants');
    } finally {
      setIsLoading(false);
    }
  };

  const handleDeleteClick = (tenant) => {
    setDeleteModal({
      isOpen: true,
      tenantId: tenant.id,
      tenantName: tenant.name,
    });
  };

  const handleConfirmDelete = async () => {
    try {
      setIsDeleting(true);
      await tenantAPI.delete(deleteModal.tenantId);
      toast.success('Tenant deleted successfully');
      setDeleteModal({ isOpen: false, tenantId: null, tenantName: '' });
      setCurrentPage(1);
      fetchTenants();
    } catch (error) {
      console.error('Error deleting tenant:', error);
      toast.error('Failed to delete tenant');
    } finally {
      setIsDeleting(false);
    }
  };

  const handleCloseDeleteModal = () => {
    setDeleteModal({ isOpen: false, tenantId: null, tenantName: '' });
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
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Tenants</h1>
          <p className="text-gray-600 mt-1">Manage system tenants</p>
        </div>
        <Button
          variant="primary"
          onClick={() => setTenantModal({ isOpen: true, editingId: null })}
        >
          <Plus className="h-4 w-4" />
          New Tenant
        </Button>
      </div>

      <Input
        type="text"
        placeholder="Search tenants by name or domain..."
        value={searchTerm}
        onChange={(e) => {
          setSearchTerm(e.target.value);
          setCurrentPage(1);
        }}
        containerClassName="w-full"
      />

      {isLoading && (
        <div className="flex items-center justify-center py-20">
          <Spinner size="lg" />
        </div>
      )}

      {!isLoading && tenants.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  🏢
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">
                {searchTerm ? 'No tenants found' : 'No tenants yet'}
              </h3>
              <p className="text-gray-600 mt-2">
                {searchTerm ? 'Try adjusting your search' : 'Create your first tenant to get started'}
              </p>
              {!searchTerm && (
                <Button
                  variant="primary"
                  className="mt-4"
                  onClick={() => setTenantModal({ isOpen: true, editingId: null })}
                >
                  Create Tenant
                </Button>
              )}
            </div>
          </CardContent>
        </Card>
      )}

      {!isLoading && tenants.length > 0 && (
        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHeadCell>Name</TableHeadCell>
                <TableHeadCell>Domain</TableHeadCell>
                <TableHeadCell>Plan</TableHeadCell>
                <TableHeadCell>Owner</TableHeadCell>
                <TableHeadCell>Status</TableHeadCell>
                <TableHeadCell>Created</TableHeadCell>
                <TableHeadCell className="text-right">Actions</TableHeadCell>
              </TableRow>
            </TableHeader>
            <TableBody>
              {tenants.map((tenant) => (
                <TableRow key={tenant.id}>
                  <TableCell className="font-medium text-gray-900">{tenant.name}</TableCell>
                  <TableCell className="text-gray-600 font-mono text-sm">{tenant.domain}</TableCell>
                  <TableCell>
                    <Badge variant="primary" className="capitalize">
                      {tenant.plan || 'free'}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-gray-600">{tenant.owner_name || 'N/A'}</TableCell>
                  <TableCell>
                    <Badge variant={tenant.is_active ? 'success' : 'default'}>
                      {tenant.is_active ? 'Active' : 'Inactive'}
                    </Badge>
                  </TableCell>
                  <TableCell className="text-gray-600 text-sm">
                    {formatDate(tenant.created_at)}
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setTenantModal({ isOpen: true, editingId: tenant.id })}
                      >
                        <Edit2 className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="danger"
                        size="sm"
                        onClick={() => handleDeleteClick(tenant)}
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

      <TenantModal
        isOpen={tenantModal.isOpen}
        onClose={() => setTenantModal({ isOpen: false, editingId: null })}
        tenantId={tenantModal.editingId}
        onSuccess={() => {
          setTenantModal({ isOpen: false, editingId: null });
          setCurrentPage(1);
          fetchTenants();
        }}
      />

      <Modal
        isOpen={deleteModal.isOpen}
        onClose={handleCloseDeleteModal}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={handleCloseDeleteModal}>
          <ModalTitle>Delete Tenant</ModalTitle>
        </ModalHeader>
        <ModalContent>
          <p className="text-gray-600">
            Are you sure you want to delete <strong>{deleteModal.tenantName}</strong>? This action cannot be undone.
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

const TenantModal = ({ isOpen, onClose, tenantId, onSuccess }) => {
  const [isLoading, setIsLoading] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    domain: '',
    owner_email: '',
    plan: 'free',
    is_active: true,
  });

  useEffect(() => {
    if (isOpen && tenantId) {
      loadTenant();
    } else if (isOpen) {
      setFormData({
        name: '',
        domain: '',
        owner_email: '',
        plan: 'free',
        is_active: true,
      });
    }
  }, [isOpen, tenantId]);

  const loadTenant = async () => {
    try {
      setIsLoading(true);
      const response = await tenantAPI.get(tenantId);
      const tenant = response.data.data;

      setFormData({
        name: tenant.name,
        domain: tenant.domain,
        owner_email: tenant.owner_email || '',
        plan: tenant.plan || 'free',
        is_active: tenant.is_active,
      });
    } catch (error) {
      console.error('Error loading tenant:', error);
      toast.error('Failed to load tenant');
    } finally {
      setIsLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      setIsLoading(true);

      if (tenantId) {
        await tenantAPI.update(tenantId, formData);
        toast.success('Tenant updated successfully');
      } else {
        await tenantAPI.create(formData);
        toast.success('Tenant created successfully');
      }

      onSuccess();
    } catch (error) {
      console.error('Error saving tenant:', error);
      toast.error(error.response?.data?.message || 'Failed to save tenant');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-md">
      <ModalHeader onClose={onClose}>
        <ModalTitle>{tenantId ? 'Edit Tenant' : 'Create Tenant'}</ModalTitle>
      </ModalHeader>

      {isLoading && tenantId ? (
        <ModalContent className="flex items-center justify-center py-8">
          <Spinner />
        </ModalContent>
      ) : (
        <form onSubmit={handleSubmit}>
          <ModalContent className="space-y-4">
            <Input
              label="Tenant Name"
              placeholder="Acme Corp"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
            />

            <Input
              label="Domain"
              placeholder="acme.example.com"
              name="domain"
              value={formData.domain}
              onChange={handleChange}
              required
            />

            <Input
              label="Owner Email"
              type="email"
              placeholder="owner@example.com"
              name="owner_email"
              value={formData.owner_email}
              onChange={handleChange}
            />

            <div>
              <label className="mb-2 text-sm font-medium text-gray-700 block">Plan</label>
              <select
                name="plan"
                value={formData.plan}
                onChange={handleChange}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option value="free">Free</option>
                <option value="pro">Pro</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>

            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                name="is_active"
                checked={formData.is_active}
                onChange={handleChange}
                className="rounded"
              />
              <span className="text-sm font-medium text-gray-700">Active</span>
            </label>
          </ModalContent>

          <ModalFooter>
            <Button variant="secondary" onClick={onClose} disabled={isLoading}>
              Cancel
            </Button>
            <Button variant="primary" type="submit" isLoading={isLoading}>
              {tenantId ? 'Update Tenant' : 'Create Tenant'}
            </Button>
          </ModalFooter>
        </form>
      )}
    </Modal>
  );
};

export default Tenants;

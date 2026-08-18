import { useState, useEffect } from 'react';
import { Plus, Edit2, Trash2, Eye, ChevronUp, ChevronDown } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import toast from 'react-hot-toast';
import { useForm, useFieldArray } from 'react-hook-form';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Badge from '../../components/ui/Badge';
import Spinner from '../../components/ui/Spinner';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { Table, TableHeader, TableBody, TableRow, TableCell, TableHeadCell, Pagination } from '../../components/ui/Table';
import { formAPI } from '../../lib/api';
import { useAuth } from '../../lib/auth';

const Forms = () => {
  const { currentTenant, isSuperAdmin } = useAuth();
  const navigate = useNavigate();
  const [forms, setForms] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [deleteModal, setDeleteModal] = useState({ isOpen: false, formId: null, formName: '' });
  const [isDeleting, setIsDeleting] = useState(false);
  const [formModal, setFormModal] = useState({ isOpen: false, editingId: null });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const itemsPerPage = 10;

  useEffect(() => {
    if (currentTenant || isSuperAdmin) {
      fetchForms();
    } else {
      setIsLoading(false);
    }
  }, [currentPage, currentTenant, isSuperAdmin]);

  const fetchForms = async () => {
    try {
      setIsLoading(true);
      const response = await formAPI.list({
        page: currentPage,
        per_page: itemsPerPage,
      });

      const data = response.data.data || [];
      setForms(data);

      const total = response.data.meta?.total || data.length;
      setTotalPages(Math.ceil(total / itemsPerPage));
    } catch (error) {
      console.error('Error fetching forms:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load forms');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleDeleteClick = (form) => {
    setDeleteModal({
      isOpen: true,
      formId: form.id,
      formName: form.name,
    });
  };

  const handleConfirmDelete = async () => {
    try {
      setIsDeleting(true);
      await formAPI.delete(deleteModal.formId);
      toast.success('Form deleted successfully');
      setDeleteModal({ isOpen: false, formId: null, formName: '' });
      setCurrentPage(1);
      fetchForms();
    } catch (error) {
      console.error('Error deleting form:', error);
      toast.error('Failed to delete form');
    } finally {
      setIsDeleting(false);
    }
  };

  const handleCloseDeleteModal = () => {
    setDeleteModal({ isOpen: false, formId: null, formName: '' });
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
          <h1 className="text-3xl font-bold text-gray-900">Forms</h1>
          <p className="text-gray-600 mt-1">Manage your website forms</p>
        </div>
        <Button
          variant="primary"
          onClick={() => setFormModal({ isOpen: true, editingId: null })}
        >
          <Plus className="h-4 w-4" />
          New Form
        </Button>
      </div>

      {isLoading && (
        <div className="flex items-center justify-center py-20">
          <Spinner size="lg" />
        </div>
      )}

      {!isLoading && forms.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  📋
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">No forms yet</h3>
              <p className="text-gray-600 mt-2">
                Create your first form to get started
              </p>
              <Button
                variant="primary"
                className="mt-4"
                onClick={() => setFormModal({ isOpen: true, editingId: null })}
              >
                Create Form
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {!isLoading && forms.length > 0 && (
        <Card>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHeadCell>Name</TableHeadCell>
                <TableHeadCell>Slug</TableHeadCell>
                <TableHeadCell>Fields</TableHeadCell>
                <TableHeadCell>Submissions</TableHeadCell>
                <TableHeadCell>Status</TableHeadCell>
                <TableHeadCell className="text-right">Actions</TableHeadCell>
              </TableRow>
            </TableHeader>
            <TableBody>
              {forms.map((form) => (
                <TableRow key={form.id}>
                  <TableCell className="font-medium text-gray-900">{form.name}</TableCell>
                  <TableCell className="text-gray-600">{form.slug}</TableCell>
                  <TableCell className="text-gray-600">{form.fields_count || 0}</TableCell>
                  <TableCell className="text-gray-600">{form.submissions_count || 0}</TableCell>
                  <TableCell>
                    <Badge variant={form.is_active ? 'success' : 'default'}>
                      {form.is_active ? 'Active' : 'Inactive'}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center justify-end gap-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        title="View submissions"
                        onClick={() => navigate(`/dashboard/forms/${form.id}/submissions`)}
                      >
                        <Eye className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => setFormModal({ isOpen: true, editingId: form.id })}
                      >
                        <Edit2 className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="danger"
                        size="sm"
                        onClick={() => handleDeleteClick(form)}
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

      <FormModal
        isOpen={formModal.isOpen}
        onClose={() => setFormModal({ isOpen: false, editingId: null })}
        formId={formModal.editingId}
        onSuccess={() => {
          setFormModal({ isOpen: false, editingId: null });
          setCurrentPage(1);
          fetchForms();
        }}
      />

      <Modal
        isOpen={deleteModal.isOpen}
        onClose={handleCloseDeleteModal}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={handleCloseDeleteModal}>
          <ModalTitle>Delete Form</ModalTitle>
        </ModalHeader>
        <ModalContent>
          <p className="text-gray-600">
            Are you sure you want to delete <strong>{deleteModal.formName}</strong>? This action cannot be undone.
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

const FormModal = ({ isOpen, onClose, formId, onSuccess }) => {
  const { register, control, handleSubmit, reset, watch, formState: { errors } } = useForm({
    defaultValues: {
      name: '',
      slug: '',
      notification_email: '',
      success_message: '',
      is_active: true,
      fields: [],
    },
  });

  const { fields, append, remove, move } = useFieldArray({
    control,
    name: 'fields',
  });

  const [isLoading, setIsLoading] = useState(false);
  const [initialData, setInitialData] = useState(null);

  const nameValue = watch('name');

  useEffect(() => {
    if (isOpen && formId) {
      loadForm();
    } else if (isOpen) {
      reset({
        name: '',
        slug: '',
        notification_email: '',
        success_message: '',
        is_active: true,
        fields: [],
      });
    }
  }, [isOpen, formId, reset]);

  const loadForm = async () => {
    try {
      setIsLoading(true);
      const response = await formAPI.get(formId);
      const formData = response.data.data;
      setInitialData(formData);

      reset({
        name: formData.name,
        slug: formData.slug,
        notification_email: formData.notification_email || '',
        success_message: formData.success_message || '',
        is_active: formData.is_active,
        fields: formData.fields || [],
      });
    } catch (error) {
      console.error('Error loading form:', error);
      toast.error('Failed to load form');
    } finally {
      setIsLoading(false);
    }
  };

  const generateSlug = (name) => {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '');
  };

  const onSubmit = async (data) => {
    try {
      setIsLoading(true);

      const submitData = {
        ...data,
        slug: data.slug || generateSlug(data.name),
      };

      if (formId) {
        await formAPI.update(formId, submitData);
        toast.success('Form updated successfully');
      } else {
        await formAPI.create(submitData);
        toast.success('Form created successfully');
      }

      onSuccess();
    } catch (error) {
      console.error('Error saving form:', error);
      toast.error(error.response?.data?.message || 'Failed to save form');
    } finally {
      setIsLoading(false);
    }
  };

  const addField = () => {
    append({
      name: '',
      label: '',
      type: 'text',
      required: false,
      placeholder: '',
      options: [],
    });
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} className="w-full max-w-2xl">
      <ModalHeader onClose={onClose}>
        <ModalTitle>{formId ? 'Edit Form' : 'Create Form'}</ModalTitle>
      </ModalHeader>

      {isLoading && formId ? (
        <ModalContent className="flex items-center justify-center py-8">
          <Spinner />
        </ModalContent>
      ) : (
        <form onSubmit={handleSubmit(onSubmit)}>
          <ModalContent className="space-y-4">
            <Input
              label="Form Name"
              placeholder="Contact Form"
              {...register('name', { required: 'Form name is required' })}
              error={errors.name?.message}
            />

            <Input
              label="Form Slug"
              placeholder="contact-form"
              {...register('slug')}
              error={errors.slug?.message}
            />

            <Input
              label="Notification Email"
              type="email"
              placeholder="admin@example.com"
              {...register('notification_email', {
                pattern: {
                  value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                  message: 'Invalid email address',
                },
              })}
              error={errors.notification_email?.message}
            />

            <Input
              label="Success Message"
              placeholder="Thank you for your submission"
              {...register('success_message')}
              error={errors.success_message?.message}
            />

            <label className="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" {...register('is_active')} className="rounded" />
              <span className="text-sm font-medium text-gray-700">Active</span>
            </label>

            <div className="border-t pt-4">
              <div className="flex items-center justify-between mb-4">
                <h3 className="font-semibold text-gray-900">Form Fields</h3>
                <Button type="button" variant="secondary" size="sm" onClick={addField}>
                  <Plus className="h-4 w-4" />
                  Add Field
                </Button>
              </div>

              {fields.length === 0 ? (
                <p className="text-gray-600 text-sm py-4 text-center">No fields yet. Add your first field.</p>
              ) : (
                <div className="space-y-3">
                  {fields.map((field, index) => (
                    <FormFieldRow
                      key={field.id}
                      index={index}
                      field={field}
                      control={control}
                      register={register}
                      remove={remove}
                      move={move}
                      fieldsLength={fields.length}
                    />
                  ))}
                </div>
              )}
            </div>
          </ModalContent>

          <ModalFooter>
            <Button variant="secondary" onClick={onClose} disabled={isLoading}>
              Cancel
            </Button>
            <Button variant="primary" type="submit" isLoading={isLoading}>
              {formId ? 'Update Form' : 'Create Form'}
            </Button>
          </ModalFooter>
        </form>
      )}
    </Modal>
  );
};

const FormFieldRow = ({ index, field, control, register, remove, move, fieldsLength }) => {
  return (
    <div className="border border-gray-200 rounded-lg p-3 space-y-3">
      <div className="grid grid-cols-2 gap-3">
        <div>
          <label className="mb-2 text-sm font-medium text-gray-700 block">Field Name</label>
          <input
            type="text"
            placeholder="first_name"
            {...register(`fields.${index}.name`, { required: 'Field name is required' })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label className="mb-2 text-sm font-medium text-gray-700 block">Field Label</label>
          <input
            type="text"
            placeholder="First Name"
            {...register(`fields.${index}.label`, { required: 'Field label is required' })}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div>
          <label className="mb-2 text-sm font-medium text-gray-700 block">Field Type</label>
          <select
            {...register(`fields.${index}.type`)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="text">Text</option>
            <option value="email">Email</option>
            <option value="tel">Phone</option>
            <option value="textarea">Textarea</option>
            <option value="select">Select</option>
            <option value="checkbox">Checkbox</option>
            <option value="radio">Radio</option>
            <option value="number">Number</option>
            <option value="date">Date</option>
            <option value="url">URL</option>
            <option value="hidden">Hidden</option>
          </select>
        </div>

        <div>
          <label className="mb-2 text-sm font-medium text-gray-700 block">Placeholder</label>
          <input
            type="text"
            placeholder="Enter placeholder text"
            {...register(`fields.${index}.placeholder`)}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          />
        </div>
      </div>

      <div className="flex items-center gap-2">
        <label className="flex items-center gap-2 cursor-pointer flex-1">
          <input type="checkbox" {...register(`fields.${index}.required`)} className="rounded" />
          <span className="text-sm font-medium text-gray-700">Required</span>
        </label>

        <div className="flex gap-1">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={() => move(index, index - 1)}
            disabled={index === 0}
          >
            <ChevronUp className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            onClick={() => move(index, index + 1)}
            disabled={index === fieldsLength - 1}
          >
            <ChevronDown className="h-4 w-4" />
          </Button>
          <Button
            type="button"
            variant="danger"
            size="sm"
            onClick={() => remove(index)}
          >
            <Trash2 className="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  );
};

export default Forms;

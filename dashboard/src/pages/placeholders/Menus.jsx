import { useState, useEffect } from 'react';
import { Plus, Trash2, ChevronDown } from 'lucide-react';
import toast from 'react-hot-toast';
import Button from '../../components/ui/Button';
import Input from '../../components/ui/Input';
import Spinner from '../../components/ui/Spinner';
import Modal, { ModalHeader, ModalTitle, ModalContent, ModalFooter } from '../../components/ui/Modal';
import { Card, CardContent, CardHeader, CardTitle } from '../../components/ui/Card';
import { menuAPI } from '../../lib/api';
import { useAuth } from '../../lib/auth';

const LOCATIONS = ['header', 'footer', 'sidebar', 'mobile', 'custom'];

const Menus = () => {
  const { currentTenant, isSuperAdmin } = useAuth();
  const [menus, setMenus] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [expandedMenuId, setExpandedMenuId] = useState(null);
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [deleteMenuId, setDeleteMenuId] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [editingMenus, setEditingMenus] = useState({});

  const [newMenuForm, setNewMenuForm] = useState({
    name: '',
    slug: '',
    location: 'header',
  });

  useEffect(() => {
    if (currentTenant || isSuperAdmin) {
      fetchMenus();
    } else {
      setIsLoading(false);
    }
  }, [currentTenant, isSuperAdmin]);

  const fetchMenus = async () => {
    try {
      setIsLoading(true);
      const response = await menuAPI.list({ per_page: 100 });
      const menusData = response.data.data || [];
      setMenus(menusData);
      setEditingMenus({});
    } catch (error) {
      console.error('Error fetching menus:', error);
      if (error.response?.status !== 400) {
        toast.error('Failed to load menus');
      }
    } finally {
      setIsLoading(false);
    }
  };

  const handleCreateMenu = async () => {
    if (!newMenuForm.name.trim() || !newMenuForm.slug.trim()) {
      toast.error('Please fill in all fields');
      return;
    }

    try {
      setIsSaving(true);
      await menuAPI.create({
        name: newMenuForm.name,
        slug: newMenuForm.slug,
        location: newMenuForm.location,
        items: [],
      });
      toast.success('Menu created successfully');
      setNewMenuForm({ name: '', slug: '', location: 'header' });
      setCreateModalOpen(false);
      fetchMenus();
    } catch (error) {
      console.error('Error creating menu:', error);
      toast.error('Failed to create menu');
    } finally {
      setIsSaving(false);
    }
  };

  const handleDeleteMenu = async () => {
    try {
      setIsDeleting(true);
      await menuAPI.delete(deleteMenuId);
      toast.success('Menu deleted successfully');
      setDeleteMenuId(null);
      fetchMenus();
    } catch (error) {
      console.error('Error deleting menu:', error);
      toast.error('Failed to delete menu');
    } finally {
      setIsDeleting(false);
    }
  };

  const handleAddMenuItem = (menuId) => {
    if (!editingMenus[menuId]) {
      editingMenus[menuId] = { items: menus.find((m) => m.id === menuId)?.items || [] };
    }

    const newItem = {
      id: Date.now(),
      title: '',
      url: '',
      target: '_self',
      order: editingMenus[menuId].items.length,
    };

    const updated = { ...editingMenus };
    updated[menuId] = {
      ...updated[menuId],
      items: [...(updated[menuId].items || []), newItem],
    };
    setEditingMenus(updated);
  };

  const handleUpdateMenuItem = (menuId, itemId, field, value) => {
    const updated = { ...editingMenus };
    if (!updated[menuId]) {
      updated[menuId] = { items: menus.find((m) => m.id === menuId)?.items || [] };
    }

    updated[menuId].items = updated[menuId].items.map((item) =>
      item.id === itemId ? { ...item, [field]: value } : item
    );
    setEditingMenus(updated);
  };

  const handleRemoveMenuItem = (menuId, itemId) => {
    const updated = { ...editingMenus };
    if (updated[menuId]) {
      updated[menuId].items = updated[menuId].items.filter(
        (item) => item.id !== itemId
      );
      setEditingMenus(updated);
    }
  };

  const handleSaveMenu = async (menuId) => {
    try {
      setIsSaving(true);
      const menu = menus.find((m) => m.id === menuId);
      const items = editingMenus[menuId]?.items || menu.items || [];

      await menuAPI.update(menuId, {
        name: menu.name,
        slug: menu.slug,
        location: menu.location,
        items: items.map((item, idx) => ({
          ...item,
          order: idx,
        })),
      });

      toast.success('Menu updated successfully');
      setEditingMenus({ ...editingMenus, [menuId]: undefined });
      fetchMenus();
    } catch (error) {
      console.error('Error updating menu:', error);
      toast.error('Failed to update menu');
    } finally {
      setIsSaving(false);
    }
  };

  const getMenuItems = (menuId) => {
    return editingMenus[menuId]?.items || menus.find((m) => m.id === menuId)?.items || [];
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center py-20">
        <Spinner size="lg" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Menus</h1>
          <p className="text-gray-600 mt-1">Manage your website menus and navigation</p>
        </div>
        <Button
          variant="primary"
          onClick={() => setCreateModalOpen(true)}
        >
          <Plus className="h-4 w-4" />
          New Menu
        </Button>
      </div>

      {/* Empty State */}
      {menus.length === 0 && (
        <Card>
          <CardContent className="pt-12">
            <div className="text-center">
              <div className="text-gray-400 mb-4">
                <div className="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                  🎯
                </div>
              </div>
              <h3 className="text-lg font-medium text-gray-900">No menus yet</h3>
              <p className="text-gray-600 mt-2">
                Create your first menu to get started
              </p>
              <Button
                variant="primary"
                className="mt-4"
                onClick={() => setCreateModalOpen(true)}
              >
                Create Menu
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Menus List */}
      {menus.length > 0 && (
        <div className="space-y-4">
          {menus.map((menu) => (
            <Card key={menu.id}>
              <CardHeader
                className="cursor-pointer hover:bg-gray-50 transition-colors"
                onClick={() =>
                  setExpandedMenuId(
                    expandedMenuId === menu.id ? null : menu.id
                  )
                }
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3 flex-1">
                    <ChevronDown
                      className={`h-5 w-5 text-gray-400 transition-transform ${
                        expandedMenuId === menu.id ? 'rotate-180' : ''
                      }`}
                    />
                    <div>
                      <CardTitle className="text-lg">{menu.name}</CardTitle>
                      <p className="text-sm text-gray-600 mt-1">
                        Slug: <code className="bg-gray-100 px-2 py-1 rounded">{menu.slug}</code>
                      </p>
                      <p className="text-sm text-gray-600">
                        Location: <span className="font-medium">{menu.location}</span> • Items: {(menu.items || []).length}
                      </p>
                    </div>
                  </div>
                  <Button
                    variant="danger"
                    size="sm"
                    onClick={(e) => {
                      e.stopPropagation();
                      setDeleteMenuId(menu.id);
                    }}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </CardHeader>

              {expandedMenuId === menu.id && (
                <CardContent className="space-y-4 border-t border-gray-200 pt-4">
                  {/* Menu Items */}
                  <div className="space-y-3">
                    {getMenuItems(menu.id).length === 0 ? (
                      <p className="text-gray-500 text-center py-4">
                        No items in this menu
                      </p>
                    ) : (
                      getMenuItems(menu.id).map((item, itemIndex) => (
                        <div
                          key={item.id}
                          className="p-3 bg-gray-50 rounded-lg space-y-2"
                        >
                          <div className="flex items-center gap-2">
                            <span className="text-sm font-medium text-gray-600">
                              Item {itemIndex + 1}
                            </span>
                            <Button
                              type="button"
                              variant="danger"
                              size="sm"
                              onClick={() => handleRemoveMenuItem(menu.id, item.id)}
                            >
                              <Trash2 className="h-3 w-3" />
                            </Button>
                          </div>

                          <div className="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <Input
                              label="Title"
                              placeholder="Menu item title"
                              value={item.title || ''}
                              onChange={(e) =>
                                handleUpdateMenuItem(
                                  menu.id,
                                  item.id,
                                  'title',
                                  e.target.value
                                )
                              }
                              containerClassName="w-full"
                            />

                            <Input
                              label="URL"
                              placeholder="/page or https://example.com"
                              value={item.url || ''}
                              onChange={(e) =>
                                handleUpdateMenuItem(
                                  menu.id,
                                  item.id,
                                  'url',
                                  e.target.value
                                )
                              }
                              containerClassName="w-full"
                            />
                          </div>

                          <div>
                            <label className="mb-1 text-sm font-medium text-gray-700 block">
                              Target
                            </label>
                            <select
                              value={item.target || '_self'}
                              onChange={(e) =>
                                handleUpdateMenuItem(
                                  menu.id,
                                  item.id,
                                  'target',
                                  e.target.value
                                )
                              }
                              className="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                              <option value="_self">Same Window</option>
                              <option value="_blank">New Window</option>
                            </select>
                          </div>
                        </div>
                      ))
                    )}
                  </div>

                  {/* Add Item Button */}
                  <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    onClick={() => handleAddMenuItem(menu.id)}
                    className="w-full"
                  >
                    <Plus className="h-4 w-4" />
                    Add Item
                  </Button>

                  {/* Save Button */}
                  {editingMenus[menu.id] && (
                    <Button
                      type="button"
                      variant="primary"
                      onClick={() => handleSaveMenu(menu.id)}
                      isLoading={isSaving}
                      className="w-full"
                    >
                      Save Changes
                    </Button>
                  )}
                </CardContent>
              )}
            </Card>
          ))}
        </div>
      )}

      {/* Create Menu Modal */}
      <Modal
        isOpen={createModalOpen}
        onClose={() => setCreateModalOpen(false)}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={() => setCreateModalOpen(false)}>
          <ModalTitle>Create New Menu</ModalTitle>
        </ModalHeader>
        <ModalContent className="space-y-4">
          <Input
            label="Menu Name"
            placeholder="e.g., Main Navigation"
            value={newMenuForm.name}
            onChange={(e) =>
              setNewMenuForm({ ...newMenuForm, name: e.target.value })
            }
            containerClassName="w-full"
          />

          <Input
            label="Slug"
            placeholder="e.g., main-nav"
            value={newMenuForm.slug}
            onChange={(e) =>
              setNewMenuForm({ ...newMenuForm, slug: e.target.value })
            }
            containerClassName="w-full"
          />

          <div>
            <label className="mb-2 text-sm font-medium text-gray-700 block">
              Location
            </label>
            <select
              value={newMenuForm.location}
              onChange={(e) =>
                setNewMenuForm({ ...newMenuForm, location: e.target.value })
              }
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              {LOCATIONS.map((loc) => (
                <option key={loc} value={loc}>
                  {loc.charAt(0).toUpperCase() + loc.slice(1)}
                </option>
              ))}
            </select>
          </div>
        </ModalContent>
        <ModalFooter>
          <Button
            variant="secondary"
            onClick={() => setCreateModalOpen(false)}
            disabled={isSaving}
          >
            Cancel
          </Button>
          <Button
            variant="primary"
            onClick={handleCreateMenu}
            isLoading={isSaving}
          >
            Create Menu
          </Button>
        </ModalFooter>
      </Modal>

      {/* Delete Menu Modal */}
      <Modal
        isOpen={!!deleteMenuId}
        onClose={() => setDeleteMenuId(null)}
        className="w-full max-w-md"
      >
        <ModalHeader onClose={() => setDeleteMenuId(null)}>
          <ModalTitle>Delete Menu</ModalTitle>
        </ModalHeader>
        <ModalContent>
          <p className="text-gray-600">
            Are you sure you want to delete this menu? This action cannot be undone.
          </p>
        </ModalContent>
        <ModalFooter>
          <Button
            variant="secondary"
            onClick={() => setDeleteMenuId(null)}
            disabled={isDeleting}
          >
            Cancel
          </Button>
          <Button
            variant="danger"
            onClick={handleDeleteMenu}
            isLoading={isDeleting}
          >
            Delete
          </Button>
        </ModalFooter>
      </Modal>
    </div>
  );
};

export default Menus;

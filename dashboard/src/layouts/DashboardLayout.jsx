import { useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import {
  LayoutDashboard,
  FileText,
  Menu,
  FormInput,
  Image,
  Settings,
  Search,
  Building2,
  LogOut,
  ChevronDown,
  Menu as MenuIcon,
  X,
} from 'lucide-react';
import { useAuth } from '../lib/auth';
import Button from '../components/ui/Button';

const DashboardLayout = () => {
  const { user, currentTenant, tenants, isSuperAdmin, logout, switchTenant } =
    useAuth();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [tenantDropdownOpen, setTenantDropdownOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);

  const navItems = [
    { path: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { path: '/dashboard/pages', label: 'Pages', icon: FileText },
    { path: '/dashboard/menus', label: 'Menus', icon: Menu },
    { path: '/dashboard/forms', label: 'Forms', icon: FormInput },
    { path: '/dashboard/media', label: 'Media', icon: Image },
    { path: '/dashboard/seo', label: 'SEO', icon: Search },
    { path: '/dashboard/settings', label: 'Settings', icon: Settings },
  ];

  const adminNavItems = isSuperAdmin
    ? [{ path: '/dashboard/tenants', label: 'Tenants', icon: Building2 }]
    : [];

  const allNavItems = [...navItems, ...adminNavItems];

  const isActive = (path) => location.pathname === path;

  const getPageTitle = () => {
    const active = allNavItems.find((item) => isActive(item.path));
    return active ? active.label : 'Dashboard';
  };

  return (
    <div className="flex h-screen bg-gray-50">
      {/* Sidebar */}
      <div
        className={`${
          sidebarOpen ? 'w-64' : 'w-0'
        } transition-all duration-300 overflow-hidden bg-gray-900 text-white flex flex-col`}
      >
        {/* Logo */}
        <div className="px-6 py-8 border-b border-gray-800">
          <h1 className="text-2xl font-bold text-white">TuxCMS</h1>
        </div>

        {/* Navigation */}
        <nav className="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
          {allNavItems.map((item) => {
            const Icon = item.icon;
            const active = isActive(item.path);
            return (
              <Link
                key={item.path}
                to={item.path}
                className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ${
                  active
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-300 hover:bg-gray-800'
                }`}
              >
                <Icon className="h-5 w-5" />
                <span className="text-sm font-medium">{item.label}</span>
              </Link>
            );
          })}
        </nav>

        {/* Tenant Switcher */}
        {tenants.length > 1 && (
          <div className="px-4 py-4 border-t border-gray-800">
            <div className="relative">
              <button
                onClick={() => setTenantDropdownOpen(!tenantDropdownOpen)}
                className="w-full flex items-center justify-between px-4 py-3 bg-gray-800 rounded-lg hover:bg-gray-700 transition-colors text-left text-sm"
              >
                <div className="flex-1">
                  <div className="text-xs text-gray-400">Current Tenant</div>
                  <div className="font-medium text-white truncate">
                    {currentTenant?.name || 'Select Tenant'}
                  </div>
                </div>
                <ChevronDown
                  className={`h-4 w-4 ml-2 transition-transform ${
                    tenantDropdownOpen ? 'rotate-180' : ''
                  }`}
                />
              </button>

              {tenantDropdownOpen && (
                <div className="absolute bottom-full left-0 right-0 mb-2 bg-gray-800 rounded-lg border border-gray-700 shadow-lg z-50">
                  {tenants.map((tenant) => (
                    <button
                      key={tenant.id}
                      onClick={() => {
                        switchTenant(tenant.id);
                        setTenantDropdownOpen(false);
                      }}
                      className={`w-full text-left px-4 py-3 text-sm transition-colors first:rounded-t-lg last:rounded-b-lg ${
                        currentTenant?.id === tenant.id
                          ? 'bg-blue-600 text-white'
                          : 'text-gray-300 hover:bg-gray-700'
                      }`}
                    >
                      {tenant.name}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}
      </div>

      {/* Main Content */}
      <div className="flex-1 flex flex-col overflow-hidden">
        {/* Top Bar */}
        <div className="bg-white border-b border-gray-200 shadow-sm">
          <div className="px-8 py-4 flex items-center justify-between">
            <div className="flex items-center gap-4">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="p-2 hover:bg-gray-100 rounded-lg transition-colors lg:hidden"
              >
                {sidebarOpen ? (
                  <X className="h-6 w-6" />
                ) : (
                  <MenuIcon className="h-6 w-6" />
                )}
              </button>
              <h2 className="text-2xl font-bold text-gray-900">
                {getPageTitle()}
              </h2>
            </div>

            {/* User Menu */}
            <div className="flex items-center gap-4">
              <div className="relative">
                <button
                  onClick={() => setUserDropdownOpen(!userDropdownOpen)}
                  className="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors"
                >
                  <div className="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
                    {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                  </div>
                  <div className="text-left hidden sm:block">
                    <div className="text-sm font-medium text-gray-900">
                      {user?.name || 'User'}
                    </div>
                    <div className="text-xs text-gray-500">
                      {user?.email || 'email@example.com'}
                    </div>
                  </div>
                  <ChevronDown className="h-4 w-4 text-gray-600" />
                </button>

                {userDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-48 bg-white rounded-lg border border-gray-200 shadow-lg z-50">
                    <div className="px-4 py-3 border-b border-gray-200">
                      <div className="text-sm font-medium text-gray-900">
                        {user?.name || 'User'}
                      </div>
                      <div className="text-xs text-gray-500">
                        {user?.email || 'email@example.com'}
                      </div>
                    </div>
                    <button
                      onClick={() => {
                        logout();
                        setUserDropdownOpen(false);
                      }}
                      className="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors"
                    >
                      <LogOut className="h-4 w-4" />
                      Logout
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Content Area */}
        <div className="flex-1 overflow-auto">
          <div className="p-8">
            <Outlet />
          </div>
        </div>
      </div>
    </div>
  );
};

export default DashboardLayout;

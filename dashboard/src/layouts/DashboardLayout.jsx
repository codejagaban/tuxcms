import { useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import {
  LayoutDashboard,
  FileText,
  Image,
  Settings,
  Search,
  LogOut,
  ChevronDown,
  Menu as MenuIcon,
  X,
} from 'lucide-react';
import { useAuth } from '../lib/auth';
import PublishButton from '../components/PublishButton';

const navItems = [
  { path: '/dashboard', label: 'Overview', icon: LayoutDashboard },
  { path: '/dashboard/pages', label: 'Pages', icon: FileText },
  { path: '/dashboard/media', label: 'Media', icon: Image },
  { path: '/dashboard/seo', label: 'Search', icon: Search },
  { path: '/dashboard/settings', label: 'Settings', icon: Settings },
];

const DashboardLayout = () => {
  const { user, logout } = useAuth();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);

  const isActive = (path) => location.pathname === path;
  const pageTitle = navItems.find((item) => isActive(item.path))?.label || 'Dashboard';

  return (
    <div className="min-h-screen bg-[#f4f4f2] text-black lg:flex">
      {sidebarOpen && (
        <button
          type="button"
          aria-label="Close navigation"
          className="fixed inset-0 z-30 bg-black/40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-[17rem] flex-col bg-black text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-24 items-center justify-between px-7">
          <Link to="/dashboard" className="flex items-baseline gap-2" aria-label="TuxCMS overview">
            <span className="text-[1.7rem] font-black tracking-[-0.08em]">TUX</span>
            <span className="text-[0.68rem] font-medium tracking-[0.24em] text-neutral-400">CMS</span>
          </Link>
          <button
            type="button"
            onClick={() => setSidebarOpen(false)}
            className="p-2 text-neutral-400 hover:text-white lg:hidden"
            aria-label="Close navigation"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        <div className="px-7 pb-5 text-xs leading-5 text-neutral-500">
          Website operations<br />and publishing
        </div>

        <nav className="flex-1 px-3 py-3" aria-label="Dashboard navigation">
          {navItems.map((item) => {
            const Icon = item.icon;
            const active = isActive(item.path);
            return (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setSidebarOpen(false)}
                className={`mb-1 flex items-center gap-3 rounded-sm px-4 py-3 text-sm font-medium transition-colors ${
                  active ? 'bg-white text-black' : 'text-neutral-400 hover:bg-neutral-900 hover:text-white'
                }`}
              >
                <Icon className="h-[18px] w-[18px]" strokeWidth={1.7} />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>

        <div className="mx-7 border-t border-neutral-800 py-6">
          <div className="text-xs text-neutral-500">Signed in as</div>
          <div className="mt-1 truncate text-sm font-medium text-neutral-200">{user?.email}</div>
        </div>
      </aside>

      <div className="min-w-0 flex-1">
        <header className="sticky top-0 z-20 border-b border-neutral-200 bg-[#f4f4f2]/95 backdrop-blur-sm">
          <div className="mx-auto flex h-20 max-w-[96rem] items-center justify-between px-5 sm:px-8 lg:px-10">
            <div className="flex items-center gap-4">
              <button
                type="button"
                onClick={() => setSidebarOpen(true)}
                className="-ml-2 p-2 text-black hover:bg-neutral-200 lg:hidden"
                aria-label="Open navigation"
              >
                <MenuIcon className="h-5 w-5" />
              </button>
              <div>
                <div className="text-[11px] font-medium text-neutral-500">Workspace</div>
                <h1 className="text-lg font-semibold tracking-[-0.025em]">{pageTitle}</h1>
              </div>
            </div>

            <div className="flex items-center gap-2 sm:gap-3">
              <PublishButton />
              <div className="relative">
                <button
                  type="button"
                  onClick={() => setUserDropdownOpen((open) => !open)}
                  className="flex items-center gap-2 rounded-md px-2 py-2 text-left hover:bg-neutral-200 sm:px-3"
                  aria-expanded={userDropdownOpen}
                  aria-label="Open account menu"
                >
                  <span className="grid h-8 w-8 place-items-center rounded-sm bg-black text-xs font-bold text-white">
                    {user?.name?.charAt(0).toUpperCase() || 'U'}
                  </span>
                  <span className="hidden max-w-32 truncate text-sm font-medium sm:block">{user?.name || 'User'}</span>
                  <ChevronDown className="hidden h-4 w-4 text-neutral-500 sm:block" />
                </button>

                {userDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-64 rounded-md border border-neutral-200 bg-white p-2 shadow-[0_12px_30px_rgba(0,0,0,0.10)]">
                    <div className="px-3 py-3">
                      <div className="text-sm font-semibold">{user?.name || 'User'}</div>
                      <div className="mt-1 truncate text-xs text-neutral-500">{user?.email}</div>
                    </div>
                    <button
                      type="button"
                      onClick={logout}
                      className="flex w-full items-center gap-3 rounded-sm px-3 py-2.5 text-sm font-medium hover:bg-neutral-100"
                    >
                      <LogOut className="h-4 w-4" />
                      Sign out
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </header>

        <main className="dashboard-grain mx-auto max-w-[96rem] px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default DashboardLayout;

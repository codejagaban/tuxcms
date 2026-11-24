import { useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import {
  IconOverview,
  IconPages,
  IconMedia,
  IconSettings,
  IconSearch,
  SignOut,
  CaretDown,
  List,
  X,
  IconJobs,
  IconApplications,
  IconEnquiries,
} from '../lib/icons';
import { useAuth } from '../lib/auth';
import PublishButton from '../components/PublishButton';

const navItems = [
  { path: '/dashboard', label: 'Overview', icon: IconOverview },
  { path: '/dashboard/pages', label: 'Pages', icon: IconPages },
  { path: '/dashboard/jobs', label: 'Jobs', icon: IconJobs },
  { path: '/dashboard/enquiries', label: 'Enquiries', icon: IconEnquiries },
  { path: '/dashboard/applications', label: 'Applications', icon: IconApplications },
  { path: '/dashboard/media', label: 'Media', icon: IconMedia },
  { path: '/dashboard/seo', label: 'Search', icon: IconSearch },
  { path: '/dashboard/settings', label: 'Settings', icon: IconSettings },
];

const DashboardLayout = () => {
  const { user, logout } = useAuth();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);

  const isActive = (path) => location.pathname === path;
  const pageTitle = navItems.find((item) => isActive(item.path))?.label || 'Dashboard';

  return (
    <div className="canvas min-h-dvh text-[var(--color-ink)] lg:flex">
      {sidebarOpen && (
        <button
          type="button"
          aria-label="Close navigation"
          className="fixed inset-0 z-30 bg-[oklch(14.5%_0_0_/_0.4)] lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-[15rem] flex-col glass-rail text-[var(--color-ink)] transition-transform duration-200 lg:sticky lg:top-0 lg:h-dvh lg:translate-x-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-14 items-center justify-between px-5">
          <Link to="/dashboard" className="flex items-baseline gap-2" aria-label="TuxCMS overview">
            <span className="text-xl font-bold tracking-[-0.06em]">TUX</span>
            <span className="text-xs font-medium text-[var(--color-muted)]">CMS</span>
          </Link>
          <button
            type="button"
            onClick={() => setSidebarOpen(false)}
            className="icon-button lg:hidden"
            aria-label="Close navigation"
          >
            <X className="h-5 w-5" weight="bold" />
          </button>
        </div>

        <nav className="flex-1 px-3 py-2" aria-label="Dashboard navigation">
          {navItems.map((item) => {
            const Icon = item.icon;
            const active = isActive(item.path);
            return (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setSidebarOpen(false)}
                className={`mb-0.5 flex min-h-9 items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-sm transition-colors ${
                  active ? 'glass-chip font-medium text-[var(--color-ink)]' : 'text-[var(--color-muted)] hover:bg-[oklch(0%_0_0_/_0.045)] hover:text-[var(--color-ink)]'
                }`}
              >
                <Icon className="h-[18px] w-[18px] shrink-0" />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>

        <div className="mx-5 border-t border-[var(--color-rule-2)] py-4">
          <div className="text-xs text-[var(--color-muted)]">Signed in</div>
          <div className="mt-0.5 truncate text-[13px] font-medium text-[var(--color-ink)]">{user?.email}</div>
        </div>
      </aside>

      <div className="min-w-0 flex-1">
        <header className="glass-bar sticky top-0 z-20 border-b border-[oklch(0%_0_0_/_0.06)]">
          <div className="mx-auto flex h-14 max-w-[96rem] items-center justify-between px-5 sm:px-8 lg:px-10">
            <div className="flex items-center gap-4">
              <button
                type="button"
                onClick={() => setSidebarOpen(true)}
                className="icon-button -ml-2 lg:hidden"
                aria-label="Open navigation"
              >
                <List className="h-5 w-5" weight="bold" />
              </button>
              <div className="flex items-center gap-2.5 text-sm">
                <span className="hidden text-[var(--color-muted)] sm:inline">Workspace</span>
                <span className="hidden text-[var(--color-rule)] sm:inline" aria-hidden="true">/</span>
                <h1 className="font-medium">{pageTitle}</h1>
              </div>
            </div>

            <div className="flex items-center gap-2 sm:gap-3">
              <PublishButton />
              <div className="relative">
                <button
                  type="button"
                  onClick={() => setUserDropdownOpen((open) => !open)}
                  className="flex min-h-11 items-center gap-2 rounded-full px-2 py-1.5 text-left hover:bg-[oklch(0%_0_0_/_0.05)] sm:px-3"
                  aria-expanded={userDropdownOpen}
                  aria-label="Open account menu"
                >
                  <span className="grid h-7 w-7 place-items-center rounded-full bg-[var(--color-accent)] text-xs font-bold text-[var(--color-accent-ink)]">
                    {user?.name?.charAt(0).toUpperCase() || 'U'}
                  </span>
                  <span className="hidden max-w-32 truncate text-sm font-medium sm:block">{user?.name || 'User'}</span>
                  <CaretDown className="hidden h-4 w-4 text-[var(--color-muted)] sm:block" weight="bold" />
                </button>

                {userDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-64 rounded-xl border border-[var(--color-rule-2)] bg-[var(--color-paper)] p-2 shadow-[0_8px_18px_oklch(18%_0.01_95_/_0.08)]">
                    <div className="px-3 py-3">
                      <div className="text-sm font-semibold">{user?.name || 'User'}</div>
                      <div className="mt-1 truncate text-xs text-neutral-500">{user?.email}</div>
                    </div>
                    <button
                      type="button"
                      onClick={logout}
                      className="flex w-full items-center gap-3 rounded-sm px-3 py-2.5 text-sm font-medium hover:bg-neutral-100"
                    >
                      <SignOut className="h-4 w-4" />
                      Sign out
                    </button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </header>

        <main className="mx-auto min-h-[calc(100dvh-3.5rem)] max-w-[96rem] px-5 py-6 sm:px-8 lg:px-10 lg:py-7">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default DashboardLayout;

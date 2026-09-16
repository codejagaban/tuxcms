import { useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import {
  SquaresFour,
  FileText,
  ImageSquare,
  Gear,
  MagnifyingGlass,
  SignOut,
  CaretDown,
  List,
  X,
  Briefcase,
  Tray,
} from '@phosphor-icons/react';
import { useAuth } from '../lib/auth';
import PublishButton from '../components/PublishButton';

const navItems = [
  { path: '/dashboard', label: 'Overview', icon: SquaresFour },
  { path: '/dashboard/pages', label: 'Pages', icon: FileText },
  { path: '/dashboard/jobs', label: 'Jobs', icon: Briefcase },
  { path: '/dashboard/applications', label: 'Applications', icon: Tray },
  { path: '/dashboard/media', label: 'Media', icon: ImageSquare },
  { path: '/dashboard/seo', label: 'Search', icon: MagnifyingGlass },
  { path: '/dashboard/settings', label: 'Settings', icon: Gear },
];

const DashboardLayout = () => {
  const { user, logout } = useAuth();
  const location = useLocation();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [userDropdownOpen, setUserDropdownOpen] = useState(false);

  const isActive = (path) => location.pathname === path;
  const pageTitle = navItems.find((item) => isActive(item.path))?.label || 'Dashboard';

  return (
    <div className="min-h-dvh bg-[var(--color-paper-2)] text-[var(--color-ink)] lg:flex">
      {sidebarOpen && (
        <button
          type="button"
          aria-label="Close navigation"
          className="fixed inset-0 z-30 bg-[oklch(18%_0.01_95_/_0.42)] lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      <aside
        className={`fixed inset-y-0 left-0 z-40 flex w-[16.5rem] flex-col bg-[var(--color-ink)] text-[var(--color-paper)] transition-transform duration-200 lg:sticky lg:top-0 lg:h-dvh lg:translate-x-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-20 items-center justify-between px-6">
          <Link to="/dashboard" className="flex items-baseline gap-2" aria-label="TuxCMS overview">
            <span className="font-[var(--font-display)] text-[1.65rem] font-bold tracking-[-0.07em]">TUX</span>
            <span className="text-xs text-[oklch(72%_0.008_95)]">CMS</span>
          </Link>
          <button
            type="button"
            onClick={() => setSidebarOpen(false)}
            className="icon-button text-[oklch(72%_0.008_95)] lg:hidden"
            aria-label="Close navigation"
          >
            <X className="h-5 w-5" weight="bold" />
          </button>
        </div>

        <div className="px-6 pb-4 text-xs leading-5 text-[oklch(65%_0.008_95)]">
          Publishing workbench
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
                className={`mb-1 flex min-h-11 items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors ${
                  active ? 'bg-[var(--color-paper)] text-[var(--color-ink)]' : 'text-[oklch(72%_0.008_95)] hover:bg-[oklch(24%_0.01_95)] hover:text-[var(--color-paper)]'
                }`}
              >
                <Icon className="h-[19px] w-[19px]" weight={active ? 'fill' : 'regular'} />
                <span>{item.label}</span>
              </Link>
            );
          })}
        </nav>

        <div className="mx-6 border-t border-[oklch(31%_0.009_95)] py-5">
          <div className="text-xs text-[oklch(62%_0.008_95)]">Signed in</div>
          <div className="mt-1 truncate text-sm font-medium text-[var(--color-paper)]">{user?.email}</div>
        </div>
      </aside>

      <div className="min-w-0 flex-1">
        <header className="sticky top-0 z-20 border-b border-[var(--color-rule-2)] bg-[var(--color-paper-2)]">
          <div className="mx-auto flex h-20 max-w-[96rem] items-center justify-between px-5 sm:px-8 lg:px-10">
            <div className="flex items-center gap-4">
              <button
                type="button"
                onClick={() => setSidebarOpen(true)}
                className="icon-button -ml-2 lg:hidden"
                aria-label="Open navigation"
              >
                <List className="h-5 w-5" weight="bold" />
              </button>
              <div>
                <div className="text-xs text-[var(--color-muted)]">Workspace</div>
                <h1 className="text-lg font-semibold tracking-[-0.025em]">{pageTitle}</h1>
              </div>
            </div>

            <div className="flex items-center gap-2 sm:gap-3">
              <PublishButton />
              <div className="relative">
                <button
                  type="button"
                  onClick={() => setUserDropdownOpen((open) => !open)}
                  className="flex min-h-11 items-center gap-2 rounded-md px-2 py-1.5 text-left hover:bg-[var(--color-paper-3)] sm:px-3"
                  aria-expanded={userDropdownOpen}
                  aria-label="Open account menu"
                >
                  <span className="grid h-8 w-8 place-items-center rounded-sm bg-[var(--color-ink)] text-xs font-bold text-[var(--color-paper)]">
                    {user?.name?.charAt(0).toUpperCase() || 'U'}
                  </span>
                  <span className="hidden max-w-32 truncate text-sm font-medium sm:block">{user?.name || 'User'}</span>
                  <CaretDown className="hidden h-4 w-4 text-[var(--color-muted)] sm:block" weight="bold" />
                </button>

                {userDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-64 rounded-md border border-[var(--color-rule-2)] bg-[var(--color-paper)] p-2 shadow-[0_8px_18px_oklch(18%_0.01_95_/_0.08)]">
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

        <main className="dashboard-grain mx-auto min-h-[calc(100dvh-5rem)] max-w-[96rem] px-5 py-8 sm:px-8 lg:px-10 lg:py-10">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

export default DashboardLayout;

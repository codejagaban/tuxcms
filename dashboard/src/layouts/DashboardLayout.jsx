import { useEffect, useRef, useState } from 'react';
import { Outlet, Link, useLocation } from 'react-router-dom';
import { AnimatePresence, motion as Motion } from 'motion/react';
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
import { useAppearance } from '../lib/theme';
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
  const [appearance, setAppearance] = useAppearance();
  const accountMenuRef = useRef(null);
  // The item under the pointer, so a soft highlight can follow it down the rail.
  const [hoveredPath, setHoveredPath] = useState(null);

  // The account menu closes on a click or tap anywhere outside it, and on
  // Escape.
  useEffect(() => {
    if (!userDropdownOpen) return undefined;
    const onPointerDown = (event) => {
      if (!accountMenuRef.current?.contains(event.target)) setUserDropdownOpen(false);
    };
    const onKeyDown = (event) => {
      if (event.key === 'Escape') setUserDropdownOpen(false);
    };
    document.addEventListener('pointerdown', onPointerDown);
    document.addEventListener('keydown', onKeyDown);
    return () => {
      document.removeEventListener('pointerdown', onPointerDown);
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [userDropdownOpen]);

  // It also closes when the page changes. Reading the new path during the
  // render that brings it in, rather than from an effect, closes the menu in
  // that same render instead of leaving it open for one pass.
  const [menuPath, setMenuPath] = useState(location.pathname);
  if (menuPath !== location.pathname) {
    setMenuPath(location.pathname);
    setUserDropdownOpen(false);
  }

  const isActive = (path) => location.pathname === path;
  const pageTitle = navItems.find((item) => isActive(item.path))?.label || 'Dashboard';

  return (
    <div className="canvas min-h-dvh text-[var(--color-ink)] lg:flex">
      {sidebarOpen && (
        <button
          type="button"
          aria-label="Close navigation"
          className="glass-veil fixed inset-0 z-30 lg:hidden"
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

        <nav className="flex-1 px-3 py-2" aria-label="Dashboard navigation" onMouseLeave={() => setHoveredPath(null)}>
          {navItems.map((item) => {
            const Icon = item.icon;
            const active = isActive(item.path);
            return (
              <Link
                key={item.path}
                to={item.path}
                onClick={() => setSidebarOpen(false)}
                onMouseEnter={() => setHoveredPath(item.path)}
                onFocus={() => setHoveredPath(item.path)}
                onBlur={() => setHoveredPath(null)}
                className={`relative mb-0.5 flex min-h-9 items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-sm transition-colors duration-200 ${
                  active ? 'font-medium text-[var(--color-ink)]' : 'text-[var(--color-muted)] hover:text-[var(--color-ink)]'
                }`}
              >
                {/* One chip for the whole rail: it travels to whichever item
                    is current instead of each item drawing its own. */}
                {active && (
                  <Motion.span
                    layoutId="rail-current"
                    className="glass-chip absolute inset-0 rounded-lg"
                    transition={{ type: 'spring', stiffness: 420, damping: 38 }}
                  />
                )}
                <AnimatePresence>
                  {hoveredPath === item.path && !active && (
                    <Motion.span
                      layoutId="rail-hover"
                      className="absolute inset-0 rounded-lg bg-[var(--color-wash)]"
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      exit={{ opacity: 0 }}
                      transition={{ type: 'spring', stiffness: 520, damping: 40 }}
                    />
                  )}
                </AnimatePresence>
                <Icon className={`relative h-[18px] w-[18px] shrink-0 transition-colors duration-200 ${active ? 'text-[var(--color-blue)]' : ''}`} />
                <span className="relative">{item.label}</span>
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
        <header className="glass-bar sticky top-0 z-20 border-b border-[var(--color-hairline)]">
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
              <div className="relative" ref={accountMenuRef}>
                <button
                  type="button"
                  onClick={() => setUserDropdownOpen((open) => !open)}
                  className="flex min-h-11 items-center gap-2 rounded-full px-2 py-1.5 text-left hover:bg-[var(--color-wash)] sm:px-3"
                  aria-expanded={userDropdownOpen}
                  aria-label="Open account menu"
                >
                  <span className="grid h-7 w-7 place-items-center rounded-full bg-[var(--color-blue)] text-xs font-bold text-[var(--color-accent-ink)]">
                    {user?.name?.charAt(0).toUpperCase() || 'U'}
                  </span>
                  <span className="hidden max-w-32 truncate text-sm font-medium sm:block">{user?.name || 'User'}</span>
                  <CaretDown className="hidden h-4 w-4 text-[var(--color-muted)] sm:block" weight="bold" />
                </button>

                {userDropdownOpen && (
                  <div className="glass-panel enter-drop absolute right-0 mt-2 w-64 rounded-xl border border-[var(--color-rule-2)] p-2 shadow-[0_8px_18px_oklch(18%_0.01_95_/_0.08)]">
                    <div className="px-3 py-3">
                      <div className="text-sm font-semibold">{user?.name || 'User'}</div>
                      <div className="mt-1 truncate text-xs text-[var(--color-muted)]">{user?.email}</div>
                    </div>
                    <div className="px-3 pb-3">
                      <div id="appearance-label" className="mb-2 text-xs text-[var(--color-muted)]">Appearance</div>
                      <div role="radiogroup" aria-labelledby="appearance-label" className="grid grid-cols-3 gap-0.5 rounded-full bg-[var(--color-paper-3)] p-0.5">
                        {[['light', 'Light'], ['dark', 'Dark'], ['system', 'System']].map(([value, label]) => (
                          <button
                            key={value}
                            type="button"
                            role="radio"
                            aria-checked={appearance === value}
                            onClick={() => setAppearance(value)}
                            className={`min-h-8 rounded-full text-[13px] transition-colors duration-150 ${
                              appearance === value
                                ? 'bg-[var(--color-paper)] font-medium text-[var(--color-ink)] shadow-[var(--glass-chip-edge)]'
                                : 'text-[var(--color-muted)] hover:text-[var(--color-ink)]'
                            }`}
                          >
                            {label}
                          </button>
                        ))}
                      </div>
                    </div>
                    <button
                      type="button"
                      onClick={logout}
                      className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-[var(--color-wash)]"
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
          {/* Each screen arrives with a short rise out of a blur. */}
          <div key={location.pathname} className="enter-up">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  );
};

export default DashboardLayout;

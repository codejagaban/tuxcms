import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight, Files, CheckCircle, PencilSimpleLine, Image, FileText } from '../lib/icons';
import { useAuth } from '../lib/auth';
import { pageAPI, mediaAPI, enquiryAPI, jobApplicationAPI } from '../lib/api';
import { Card } from '../components/ui/Card';
import Badge from '../components/ui/Badge';
import { Skeleton, ListSkeleton } from '../components/ui/Skeleton';

const Dashboard = () => {
  const { user } = useAuth();
  const [stats, setStats] = useState({ totalPages: 0, publishedPages: 0, draftPages: 0, mediaFiles: 0 });
  const [recentPages, setRecentPages] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  // Submissions the spam filter is holding back from the inboxes.
  const [held, setHeld] = useState({ enquiries: 0, applications: 0 });

  useEffect(() => {
    const fetchData = async () => {
      try {
        setIsLoading(true);
        try {
          const pagesResponse = await pageAPI.list({ per_page: 100 });
          const pages = pagesResponse.data.data || [];
          setStats((prev) => ({
            ...prev,
            totalPages: pagesResponse.data.meta?.total || pages.length,
            publishedPages: pages.filter((page) => page.status === 'published').length,
            draftPages: pages.filter((page) => page.status === 'draft').length,
          }));
          setRecentPages(pages.slice(0, 5));
        } catch (error) {
          console.error('Error fetching pages:', error);
        }

        try {
          const mediaResponse = await mediaAPI.list({ per_page: 100 });
          const mediaFiles = mediaResponse.data.data || [];
          setStats((prev) => ({ ...prev, mediaFiles: mediaResponse.data.meta?.total || mediaFiles.length }));
        } catch (error) {
          console.error('Error fetching media:', error);
        }

        try {
          const [enquiries, applications] = await Promise.all([
            enquiryAPI.list({ per_page: 1 }),
            jobApplicationAPI.list({ per_page: 1 }),
          ]);
          setHeld({
            enquiries: enquiries.data.meta?.held || 0,
            applications: applications.data.meta?.held || 0,
          });
        } catch (error) {
          console.error('Error fetching held submissions:', error);
        }
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, []);

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="space-y-2"><Skeleton className="h-3.5 w-32" /><Skeleton className="h-6 w-44" /></div>
        <div className="grid grid-cols-2 gap-px border-y border-[var(--color-rule-2)] lg:grid-cols-4">
          {[0, 1, 2, 3].map((n) => (
            <div key={n} className="space-y-3 px-1 py-4 sm:px-5"><Skeleton className="h-3 w-16" /><Skeleton className="h-7 w-24" /></div>
          ))}
        </div>
        <ListSkeleton rows={5} />
      </div>
    );
  }

  const metrics = [
    { label: 'Pages', value: stats.totalPages, note: 'Total entries', icon: Files },
    { label: 'Published', value: stats.publishedPages, note: 'Visible now', icon: CheckCircle },
    { label: 'Drafts', value: stats.draftPages, note: 'Work in progress', icon: PencilSimpleLine },
    { label: 'Media', value: stats.mediaFiles, note: 'Stored files', icon: Image },
  ];

  return (
    <div className="space-y-6">
      <section className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
        <div>
          <p className="mb-1 text-sm text-neutral-500">Good to see you, {user?.name?.split(' ')[0]}.</p>
          <h2 className="page-heading max-w-3xl">
            Website status
          </h2>
        </div>
        <p className="max-w-sm text-sm leading-6 text-neutral-600 lg:pb-1">
          Review content, prepare changes, and publish when everything is ready.
        </p>
      </section>

      {(held.enquiries > 0 || held.applications > 0) && (
        <section aria-label="Held as spam" className="rounded-md border border-[var(--color-rule-2)] px-5 py-4 text-sm leading-6">
          <p className="font-semibold">The spam filter is holding {held.enquiries + held.applications} {held.enquiries + held.applications === 1 ? 'submission' : 'submissions'}.</p>
          <p className="mt-1 text-neutral-600">
            These were not emailed and are deleted after 30 days. Check that none is genuine:{' '}
            {held.enquiries > 0 && <Link className="font-semibold underline" to="/dashboard/enquiries?held=1">{held.enquiries} in Enquiries</Link>}
            {held.enquiries > 0 && held.applications > 0 && ' and '}
            {held.applications > 0 && <Link className="font-semibold underline" to="/dashboard/applications?held=1">{held.applications} in Applications</Link>}
            .
          </p>
        </section>
      )}

      <section aria-label="Website statistics" className="grid grid-cols-2 border-y border-[var(--color-rule-2)] lg:grid-cols-4">
        {metrics.map(({ label, value, note, icon: Icon }, index) => (
          <div
            key={label}
            className={`px-1 py-4 sm:px-5 ${index % 2 ? 'border-l border-[var(--color-rule-2)]' : ''} ${index > 1 ? 'border-t border-[var(--color-rule-2)] lg:border-t-0' : ''} ${index > 0 ? 'lg:border-l' : ''}`}
          >
            <p className="flex items-center gap-2 text-xs font-medium text-neutral-500">
              <Icon className="h-4 w-4 shrink-0" aria-hidden="true" />
              {label}
            </p>
            <p className="mt-2 flex items-baseline gap-2">
              <span className="tabular-nums text-3xl font-semibold tracking-[-0.04em]">{value}</span>
              <span className="text-xs text-neutral-500">{note}</span>
            </p>
          </div>
        ))}
      </section>

      <section>
        <div className="mb-3 flex items-end justify-between gap-4">
          <div>
            <h2 className="font-semibold tracking-[-0.02em]" style={{ fontSize: '1.125rem' }}>Recent pages</h2>
            <p className="text-sm text-neutral-500">The latest content in your workspace.</p>
          </div>
          <Link to="/dashboard/pages" className="group flex shrink-0 items-center gap-2 text-sm font-semibold">
            View all
            <ArrowUpRight className="h-4 w-4" weight="bold" />
          </Link>
        </div>

        <Card className="overflow-hidden">
          <div>
            {recentPages.length === 0 ? (
              <div className="px-6 py-16 text-center">
                <h3 className="font-semibold">No pages yet</h3>
                <p className="mt-2 text-sm text-neutral-500">Create your first page to begin shaping the site.</p>
              </div>
            ) : (
              <div>
                {recentPages.map((page, index) => (
                  <Link
                    key={page.id}
                    to={`/dashboard/pages/${page.id}/edit`}
                    className={`group grid gap-2 px-4 py-3 transition-colors hover:bg-[var(--color-paper-2)] sm:grid-cols-[minmax(0,1fr)_9rem_7rem] sm:items-center sm:px-5 ${index ? 'border-t border-[var(--color-rule-2)]' : ''}`}
                  >
                    <div className="flex min-w-0 items-center gap-3">
                      <FileText className="h-4 w-4 shrink-0 text-[var(--color-muted)]" aria-hidden="true" />
                      <div className="min-w-0">
                        <h3 className="truncate text-sm font-semibold tracking-[-0.01em]">{page.title}</h3>
                        <p className="font-data truncate text-neutral-500">{page.full_path || `/${page.slug}`}</p>
                      </div>
                    </div>
                    <Badge variant={page.status === 'published' ? 'published' : 'draft'} className="w-fit">
                      {page.status || 'draft'}
                    </Badge>
                    <p className="tabular-nums text-xs text-neutral-500 sm:text-right">
                      {new Date(page.updated_at).toLocaleDateString()}
                    </p>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </Card>
      </section>
    </div>
  );
};

export default Dashboard;

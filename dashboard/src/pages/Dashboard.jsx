import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight } from '@phosphor-icons/react';
import { useAuth } from '../lib/auth';
import { pageAPI, mediaAPI } from '../lib/api';
import { Card, CardContent } from '../components/ui/Card';
import Badge from '../components/ui/Badge';
import Spinner from '../components/ui/Spinner';

const Dashboard = () => {
  const { user } = useAuth();
  const [stats, setStats] = useState({ totalPages: 0, publishedPages: 0, draftPages: 0, mediaFiles: 0 });
  const [recentPages, setRecentPages] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

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
      } finally {
        setIsLoading(false);
      }
    };

    fetchData();
  }, []);

  if (isLoading) {
    return <div className="flex items-center justify-center py-24"><Spinner size="lg" /></div>;
  }

  const metrics = [
    { label: 'Pages', value: stats.totalPages, note: 'Total entries' },
    { label: 'Published', value: stats.publishedPages, note: 'Visible now' },
    { label: 'Drafts', value: stats.draftPages, note: 'Work in progress' },
    { label: 'Media', value: stats.mediaFiles, note: 'Stored files' },
  ];

  return (
    <div className="space-y-10">
      <section className="grid gap-8 border-b border-[var(--color-rule)] pb-10 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
        <div>
          <p className="mb-3 text-sm text-neutral-500">Good to see you, {user?.name?.split(' ')[0]}.</p>
          <h2 className="page-heading max-w-3xl">
            Website status
          </h2>
        </div>
        <p className="max-w-sm text-sm leading-6 text-neutral-600 lg:pb-1">
          Review content, prepare changes, and publish when everything is ready.
        </p>
      </section>

      <section aria-label="Website statistics" className="grid grid-cols-2 border-y border-[var(--color-rule)] lg:grid-cols-4">
        {metrics.map(({ label, value, note }, index) => (
          <div
            key={label}
            className={`px-1 py-6 sm:px-5 lg:py-8 ${index % 2 ? 'border-l border-[var(--color-rule)]' : ''} ${index > 1 ? 'border-t border-[var(--color-rule)] lg:border-t-0' : ''} ${index > 0 ? 'lg:border-l' : ''}`}
          >
            <p className="text-xs font-medium text-neutral-500">{label}</p>
            <p className="tabular-nums mt-5 text-4xl font-semibold tracking-[-0.06em] sm:text-5xl">{value}</p>
            <p className="mt-2 text-xs text-neutral-500">{note}</p>
          </div>
        ))}
      </section>

      <section>
        <div className="mb-5 flex items-end justify-between gap-4">
          <div>
            <h2 className="text-2xl font-semibold tracking-[-0.035em]">Recent pages</h2>
            <p className="mt-1 text-sm text-neutral-500">The latest content in your workspace.</p>
          </div>
          <Link to="/dashboard/pages" className="group flex shrink-0 items-center gap-2 text-sm font-semibold">
            View all
            <ArrowUpRight className="h-4 w-4" weight="bold" />
          </Link>
        </div>

        <Card className="overflow-hidden">
          <CardContent className="p-0">
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
                    className={`group grid gap-3 px-5 py-5 transition-colors hover:bg-neutral-50 sm:grid-cols-[minmax(0,1fr)_9rem_7rem] sm:items-center sm:px-6 ${index ? 'border-t border-neutral-200' : ''}`}
                  >
                    <div className="min-w-0">
                      <h3 className="truncate font-semibold tracking-[-0.015em]">{page.title}</h3>
                      <p className="mt-1 truncate text-sm text-neutral-500">{page.full_path || `/${page.slug}`}</p>
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
          </CardContent>
        </Card>
      </section>
    </div>
  );
};

export default Dashboard;

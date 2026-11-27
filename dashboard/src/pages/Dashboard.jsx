import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight, IconPages, IconPublished, IconDrafts, IconMedia, IconJobs, IconEnquiries, IconApplications } from '../lib/icons';
import { useAuth } from '../lib/auth';
import { pageAPI, mediaAPI, enquiryAPI, jobApplicationAPI, jobAPI } from '../lib/api';
import PageThumb from '../components/PageThumb';
import Count from '../components/Count';
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
  const [inbox, setInbox] = useState([]);
  const [jobs, setJobs] = useState([]);
  const [media, setMedia] = useState([]);

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
          // Most recently touched first, whatever order the list came in.
          setRecentPages([...pages].sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at)).slice(0, 4));
        } catch (error) {
          console.error('Error fetching pages:', error);
        }

        try {
          const mediaResponse = await mediaAPI.list({ per_page: 100 });
          const mediaFiles = mediaResponse.data.data || [];
          setStats((prev) => ({ ...prev, mediaFiles: mediaResponse.data.meta?.total || mediaFiles.length }));
          setMedia(mediaFiles.filter((file) => file.mime_type?.startsWith('image/')).slice(0, 8));
        } catch (error) {
          console.error('Error fetching media:', error);
        }

        try {
          const [enquiries, applications] = await Promise.all([
            enquiryAPI.list({ per_page: 4 }),
            jobApplicationAPI.list({ per_page: 4 }),
          ]);
          // One inbox: messages and applications together, newest first.
          setInbox([
            ...(enquiries.data.data || []).map((item) => ({
              key: `enquiry-${item.id}`, to: '/dashboard/enquiries', kind: item.type_label || 'Enquiry',
              name: item.name, detail: item.message || item.organisation || '', status: item.delivery_status, at: item.created_at,
            })),
            ...(applications.data.data || []).map((item) => ({
              key: `application-${item.id}`, to: '/dashboard/applications', kind: 'Application',
              name: item.name, detail: item.job?.title || '', status: item.delivery_status, at: item.created_at,
            })),
          ].sort((a, b) => new Date(b.at) - new Date(a.at)).slice(0, 5));
          setHeld({
            enquiries: enquiries.data.meta?.held || 0,
            applications: applications.data.meta?.held || 0,
          });
        } catch (error) {
          console.error('Error fetching held submissions:', error);
        }

        try {
          const jobsResponse = await jobAPI.list({ per_page: 5 });
          setJobs(jobsResponse.data.data || []);
        } catch (error) {
          console.error('Error fetching vacancies:', error);
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
        <div className="work-surface grid grid-cols-2 lg:grid-cols-4">
          {[0, 1, 2, 3].map((n) => (
            <div key={n} className="space-y-3 px-4 py-4 sm:px-5"><Skeleton className="h-3 w-16" /><Skeleton className="h-7 w-24" /></div>
          ))}
        </div>
        <ListSkeleton rows={5} />
      </div>
    );
  }

  const metrics = [
    { label: 'Pages', value: stats.totalPages, note: 'Total entries', icon: IconPages, tone: 'var(--color-blue)' },
    { label: 'Published', value: stats.publishedPages, note: 'Visible now', icon: IconPublished, tone: 'var(--color-success)' },
    { label: 'Drafts', value: stats.draftPages, note: 'Work in progress', icon: IconDrafts, tone: 'var(--color-amber)' },
    { label: 'Media', value: stats.mediaFiles, note: 'Stored files', icon: IconMedia, tone: 'var(--color-rose)' },
  ];

  return (
    <div className="space-y-6">
      <section className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
        <div>
          <p className="mb-1 text-sm text-[var(--color-muted)]">Good to see you, {user?.name?.split(' ')[0]}.</p>
          <h2 className="page-heading max-w-3xl">
            Website status
          </h2>
        </div>
        <p className="max-w-sm text-sm leading-6 text-[var(--color-muted)] lg:pb-1">
          Review content, prepare changes, and publish when everything is ready.
        </p>
      </section>

      {(held.enquiries > 0 || held.applications > 0) && (
        <section aria-label="Held as spam" className="rounded-md border border-[var(--color-rule-2)] px-5 py-4 text-sm leading-6">
          <p className="font-semibold">The spam filter is holding {held.enquiries + held.applications} {held.enquiries + held.applications === 1 ? 'submission' : 'submissions'}.</p>
          <p className="mt-1 text-[var(--color-muted)]">
            These were not emailed and are deleted after 30 days. Check that none is genuine:{' '}
            {held.enquiries > 0 && <Link className="font-semibold underline" to="/dashboard/enquiries?held=1">{held.enquiries} in Enquiries</Link>}
            {held.enquiries > 0 && held.applications > 0 && ' and '}
            {held.applications > 0 && <Link className="font-semibold underline" to="/dashboard/applications?held=1">{held.applications} in Applications</Link>}
            .
          </p>
        </section>
      )}

      <section aria-label="Website statistics" className="work-surface grid grid-cols-2 overflow-hidden lg:grid-cols-4">
        {metrics.map(({ label, value, note, icon: Icon, tone }, index) => (
          <div
            key={label}
            className={`px-4 py-4 sm:px-5 ${index % 2 ? 'border-l border-[var(--color-rule-2)]' : ''} ${index > 1 ? 'border-t border-[var(--color-rule-2)] lg:border-t-0' : ''} ${index > 0 ? 'lg:border-l' : ''}`}
          >
            <p className="flex items-center gap-2 text-xs font-medium text-[var(--color-muted)]">
              <Icon className="h-[18px] w-[18px] shrink-0" style={{ color: tone }} />
              {label}
            </p>
            <p className="mt-2 flex items-baseline gap-2">
              <span className="tabular-nums text-3xl font-semibold tracking-[-0.04em]"><Count value={value} /></span>
              <span className="text-xs text-[var(--color-muted)]">{note}</span>
            </p>
          </div>
        ))}
      </section>

      <section>
        <div className="mb-3 flex items-end justify-between gap-4">
          <div>
            <h2 className="font-semibold tracking-[-0.02em]" style={{ fontSize: '1.125rem' }}>Recent pages</h2>
            <p className="text-sm text-[var(--color-muted)]">The latest content in your workspace.</p>
          </div>
          <Link to="/dashboard/pages" className="group flex shrink-0 items-center gap-2 text-sm font-semibold text-[var(--color-blue)]">
            View all
            <ArrowUpRight className="h-4 w-4" weight="bold" />
          </Link>
        </div>

        {recentPages.length === 0 ? (
          <Card className="overflow-hidden">
            <div className="px-6 py-16 text-center">
              <h3 className="font-semibold">No pages yet</h3>
              <p className="mt-2 text-sm text-[var(--color-muted)]">Create your first page to begin shaping the site.</p>
            </div>
          </Card>
        ) : (
          <ul className="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            {recentPages.map((page) => (
              <li key={page.id} className="work-surface overflow-hidden">
                <Link to={`/dashboard/pages/${page.id}/edit`} className="block" aria-label={`Edit ${page.title}`}>
                  <PageThumb page={page} />
                  <div className="px-4 py-3">
                    <h3 className="truncate text-sm font-semibold tracking-[-0.01em]">{page.title}</h3>
                    <p className="font-data mt-0.5 truncate text-xs text-[var(--color-muted)]">{page.is_homepage ? '/' : page.path || `/${page.slug}`}</p>
                    <div className="mt-2.5 flex items-center gap-2.5">
                      {page.is_scheduled
                        ? <Badge variant="pending">Scheduled</Badge>
                        : <Badge variant={page.status === 'published' ? 'published' : 'draft'}>{page.status || 'draft'}</Badge>}
                      <span className="tabular-nums text-xs text-[var(--color-muted)]">{new Date(page.updated_at).toLocaleDateString()}</span>
                    </div>
                  </div>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </section>

      <div className="grid gap-6 lg:grid-cols-2">
        <section>
          <SectionHead title="Inbox" deck="The latest messages and applications from the site." to="/dashboard/enquiries" />
          <Card className="overflow-hidden">
            {inbox.length === 0 ? (
              <Empty icon={IconEnquiries} text="Nothing has come in yet." />
            ) : inbox.map((item, index) => (
              <Link
                key={item.key}
                to={item.to}
                className={`grid grid-cols-[1.125rem_minmax(0,1fr)_auto] items-center gap-3 px-4 py-3 transition-colors hover:bg-[var(--color-hover)] sm:px-5 ${index ? 'border-t border-[var(--color-rule-2)]' : ''}`}
              >
                {item.kind === 'Application'
                  ? <IconApplications className="h-[18px] w-[18px] text-[var(--color-blue)]" />
                  : <IconEnquiries className="h-[18px] w-[18px] text-[var(--color-blue)]" />}
                <div className="min-w-0">
                  <h3 className="truncate text-sm font-semibold tracking-[-0.01em]">{item.name}</h3>
                  <p className="truncate text-xs text-[var(--color-muted)]">{item.kind}{item.detail ? `: ${item.detail}` : ''}</p>
                </div>
                <Badge variant={deliveryTone[item.status] || 'default'}>{deliveryLabel[item.status] || 'Received'}</Badge>
              </Link>
            ))}
          </Card>
        </section>

        <section>
          <SectionHead title="Vacancies" deck="Roles on the careers pages, and those waiting." to="/dashboard/jobs" />
          <Card className="overflow-hidden">
            {jobs.length === 0 ? (
              <Empty icon={IconJobs} text="No vacancies yet." />
            ) : jobs.map((job, index) => (
              <Link
                key={job.id}
                to="/dashboard/jobs"
                className={`grid grid-cols-[1.125rem_minmax(0,1fr)_auto] items-center gap-3 px-4 py-3 transition-colors hover:bg-[var(--color-hover)] sm:px-5 ${index ? 'border-t border-[var(--color-rule-2)]' : ''}`}
              >
                <IconJobs className="h-[18px] w-[18px] text-[var(--color-blue)]" />
                <div className="min-w-0">
                  <h3 className="truncate text-sm font-semibold tracking-[-0.01em]">{job.title}</h3>
                  <p className="truncate text-xs text-[var(--color-muted)]">{[job.location, job.job_type].filter(Boolean).join(', ') || 'No details yet'}</p>
                </div>
                <Badge variant={job.status === 'published' ? 'published' : job.status === 'draft' ? 'draft' : 'default'}>
                  {job.status === 'published' ? 'Open' : job.status}
                </Badge>
              </Link>
            ))}
          </Card>
        </section>
      </div>

      {media.length > 0 && (
        <section>
          <SectionHead title="Media" deck="The newest pictures in the library." to="/dashboard/media" />
          <ul className="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
            {media.map((file) => (
              <li key={file.id} className="work-surface overflow-hidden">
                <img
                  src={file.url}
                  alt={file.custom_properties?.alt_text || file.name}
                  loading="lazy"
                  className="aspect-square w-full object-cover"
                />
              </li>
            ))}
          </ul>
        </section>
      )}
    </div>
  );
};

const deliveryTone = { sent: 'success', failed: 'danger', spam: 'warning' };
const deliveryLabel = { sent: 'Sent', failed: 'Not delivered', spam: 'Held as spam' };

const SectionHead = ({ title, deck, to }) => (
  <div className="mb-3 flex items-end justify-between gap-4">
    <div>
      <h2 className="font-semibold tracking-[-0.02em]" style={{ fontSize: '1.125rem' }}>{title}</h2>
      <p className="text-sm text-[var(--color-muted)]">{deck}</p>
    </div>
    <Link to={to} className="flex shrink-0 items-center gap-2 text-sm font-semibold text-[var(--color-blue)]">
      View all
      <ArrowUpRight className="h-4 w-4" weight="bold" />
    </Link>
  </div>
);

const Empty = ({ icon: Icon, text }) => (
  <div className="px-6 py-12 text-center text-sm text-[var(--color-muted)]">
    <Icon className="mx-auto mb-3 h-6 w-6" />
    {text}
  </div>
);

export default Dashboard;

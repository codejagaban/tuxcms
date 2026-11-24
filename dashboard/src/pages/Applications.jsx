import { useCallback, useEffect, useState } from 'react';
import { DownloadSimple, EnvelopeSimple, FileText } from '../lib/icons';
import toast from 'react-hot-toast';
import { apiErrorMessage, jobApplicationAPI } from '../lib/api';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import { ListSkeleton } from '../components/ui/Skeleton';
import { Pagination, Table, TableBody, TableCell, TableHeadCell, TableHeader, TableRow } from '../components/ui/Table';

const dateTime = (value) => value
  ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
  : 'Not available';

export default function Applications() {
  const [applications, setApplications] = useState([]);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [releasing, setReleasing] = useState(null);
  const [downloading, setDownloading] = useState(null);
  const [held, setHeld] = useState(() => new URLSearchParams(window.location.search).has('held'));

  const load = useCallback(async (page = 1) => {
    setLoading(true);
    try {
      const response = await jobApplicationAPI.list({ page, per_page: 15, ...(held ? { held: 1 } : {}) });
      setApplications(response.data.data || []);
      setMeta(response.data.meta || { current_page: 1, last_page: 1, total: 0 });
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not load applications'));
    } finally {
      setLoading(false);
    }
  }, [held]);

  useEffect(() => { load(); }, [load]);

  // The spam filter held this one by mistake: email it to the inbox after all.
  const release = async (application) => {
    setReleasing(application.id);
    try {
      const response = await jobApplicationAPI.release(application.id);
      const status = response.data.delivery_status;
      setApplications((items) => items.map((item) => (
        item.id === application.id ? { ...item, delivery_status: status, spam_reason: null } : item
      )));
      setMeta((current) => ({ ...current, held: Math.max((current.held || 1) - 1, 0) }));
      if (status === 'sent') toast.success(response.data.message);
      else toast.error(response.data.message);
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not send this on'));
    } finally {
      setReleasing(null);
    }
  };

  const download = async (application) => {
    setDownloading(application.id);
    try {
      const response = await jobApplicationAPI.downloadCV(application.id);
      const url = URL.createObjectURL(response.data);
      const link = document.createElement('a');
      link.href = url;
      link.download = application.cv_name || `application-${application.id}-cv`;
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(url);
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not download this CV'));
    } finally {
      setDownloading(null);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-heading">Applications</h1>
        <p className="page-deck">Every careers submission is stored here, including applications whose notification email could not be delivered.</p>
      </div>

      <div className="flex flex-wrap gap-2" role="group" aria-label="Filter applications">
        {[{ value: false, label: 'All' }, { value: true, label: `Held as spam${meta.held > 0 ? ` (${meta.held})` : ''}` }].map((filter) => (
          <button
            key={filter.label}
            type="button"
            aria-pressed={held === filter.value}
            onClick={() => setHeld(filter.value)}
            className={`min-h-8 rounded-md border px-3 text-[13px] font-medium ${held === filter.value
              ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-[var(--color-accent-ink)]'
              : 'border-[var(--color-rule-2)] text-[var(--color-ink-2)] hover:bg-[var(--color-paper-2)]'}`}
          >
            {filter.label}
          </button>
        ))}
      </div>

      {loading ? (
        <ListSkeleton rows={5} />
      ) : applications.length === 0 ? (
        <Card className="p-12 text-center">
          <EnvelopeSimple className="mx-auto h-9 w-9 text-[var(--color-muted)]" />
          <h2 className="mt-3 text-base font-semibold">No applications yet</h2>
          <p className="mt-2 text-sm text-[var(--color-muted)]">New submissions from the Careers page will appear here.</p>
        </Card>
      ) : (
        <Card>
          <div className="border-b border-[var(--color-rule-2)] px-6 py-4 text-sm text-[var(--color-muted)]">
            {meta.total} {meta.total === 1 ? 'application' : 'applications'} received
          </div>
          <Table>
            <TableHeader><TableRow>
              <TableHeadCell>Applicant</TableHeadCell>
              <TableHeadCell>Role</TableHeadCell>
              <TableHeadCell>Submitted</TableHeadCell>
              <TableHeadCell>Email delivery</TableHeadCell>
              <TableHeadCell className="text-right">CV</TableHeadCell>
            </TableRow></TableHeader>
            <TableBody>{applications.map((application) => (
              <TableRow key={application.id}>
                <TableCell className="min-w-64 align-top">
                  <div className="font-semibold text-[var(--color-ink)]">{application.name}</div>
                  <a className="mt-1 block text-xs text-[var(--color-muted)] hover:text-[var(--color-ink)]" href={`mailto:${application.email}`}>{application.email}</a>
                  {application.phone && <a className="mt-1 block text-xs text-[var(--color-muted)] hover:text-[var(--color-ink)]" href={`tel:${application.phone}`}>{application.phone}</a>}
                  <details className="mt-3 text-xs">
                    <summary className="cursor-pointer font-medium text-[var(--color-ink-2)]">Read cover letter</summary>
                    <p className="mt-2 max-w-xl whitespace-pre-wrap leading-5 text-[var(--color-muted)]">{application.cover_letter}</p>
                  </details>
                </TableCell>
                <TableCell className="align-top font-medium">{application.job?.title || 'Deleted role'}</TableCell>
                <TableCell className="min-w-44 align-top">{dateTime(application.created_at)}</TableCell>
                <TableCell className="align-top">
                  {application.delivery_status === 'spam' ? (
                    <Badge variant="draft">Held as spam</Badge>
                  ) : (
                    <Badge variant={application.delivery_status === 'sent' ? 'published' : 'danger'}>
                      {application.delivery_status === 'sent' ? 'Sent' : 'Needs attention'}
                    </Badge>
                  )}
                  {application.spam_reason && (
                    <p className="mt-2 max-w-48 text-xs leading-4 text-[var(--color-muted)]">
                      {application.delivery_status === 'spam' ? '' : 'Check: '}{application.spam_reason}
                    </p>
                  )}
                  {application.delivery_status === 'spam' && (
                    <p className="mt-1 max-w-48 text-xs leading-4 text-[var(--color-muted)]">Deleted automatically after 30 days.</p>
                  )}
                  {application.delivery_status === 'spam' && (
                    <Button className="mt-2" variant="secondary" size="sm" isLoading={releasing === application.id} onClick={() => release(application)}>
                      Not spam, send it
                    </Button>
                  )}
                </TableCell>
                <TableCell className="min-w-44 align-top text-right">
                  <Button variant="ghost" size="sm" isLoading={downloading === application.id} onClick={() => download(application)}>
                    {downloading !== application.id && <DownloadSimple className="h-4 w-4" weight="bold" />}
                    {application.cv_name || 'Download CV'}
                  </Button>
                </TableCell>
              </TableRow>
            ))}</TableBody>
          </Table>
          {meta.last_page > 1 && <Pagination currentPage={meta.current_page} totalPages={meta.last_page} onPageChange={load} />}
        </Card>
      )}

      <div className="flex items-start gap-3 text-sm text-[var(--color-muted)]">
        <FileText className="mt-0.5 h-4 w-4 shrink-0" />
        <p>CV files are private and can only be downloaded by a signed-in CMS user.</p>
      </div>
    </div>
  );
}

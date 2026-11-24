import { useCallback, useEffect, useState } from 'react';
import { ChatText } from '@phosphor-icons/react';
import toast from 'react-hot-toast';
import { apiErrorMessage, enquiryAPI } from '../lib/api';
import Badge from '../components/ui/Badge';
import Button from '../components/ui/Button';
import { Card } from '../components/ui/Card';
import Spinner from '../components/ui/Spinner';
import { Pagination, Table, TableBody, TableCell, TableHeadCell, TableHeader, TableRow } from '../components/ui/Table';

const dateTime = (value) => value
  ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
  : 'Not available';

const label = (key) => key.replace(/_/g, ' ').replace(/^./, (letter) => letter.toUpperCase());

const FILTERS = [
  { value: '', label: 'All' },
  { value: 'staff_request', label: 'Staff requests' },
  { value: 'registration', label: 'Registrations' },
  { value: 'contact', label: 'Enquiries' },
  { value: 'held', label: 'Held as spam' },
];

export default function Enquiries() {
  const [enquiries, setEnquiries] = useState([]);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [releasing, setReleasing] = useState(null);
  const [type, setType] = useState(() => (new URLSearchParams(window.location.search).has('held') ? 'held' : ''));

  const load = useCallback(async (page = 1) => {
    setLoading(true);
    try {
      const response = await enquiryAPI.list({ page, per_page: 15, ...(type === 'held' ? { held: 1 } : type ? { type } : {}) });
      setEnquiries(response.data.data || []);
      setMeta(response.data.meta || { current_page: 1, last_page: 1, total: 0 });
    } catch (error) {
      toast.error(apiErrorMessage(error, 'Could not load enquiries'));
    } finally {
      setLoading(false);
    }
  }, [type]);

  useEffect(() => { load(); }, [load]);

  // The spam filter held this one by mistake: email it to the inbox after all.
  const release = async (enquiry) => {
    setReleasing(enquiry.id);
    try {
      const response = await enquiryAPI.release(enquiry.id);
      const status = response.data.delivery_status;
      setEnquiries((items) => items.map((item) => (
        item.id === enquiry.id ? { ...item, delivery_status: status, spam_reason: null } : item
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

  return (
    <div className="space-y-6">
      <div>
        <h1 className="page-heading">Enquiries</h1>
        <p className="page-deck">Staff requests, candidate registrations and messages from the website. Everything is kept here, including enquiries whose notification email could not be delivered.</p>
      </div>

      <div className="flex flex-wrap gap-2" role="group" aria-label="Filter enquiries">
        {FILTERS.map((filter) => (
          <button
            key={filter.value}
            type="button"
            aria-pressed={type === filter.value}
            onClick={() => setType(filter.value)}
            className={`min-h-9 rounded-md border px-3.5 text-sm font-medium ${type === filter.value
              ? 'border-[var(--color-accent)] bg-[var(--color-accent)] text-[var(--color-accent-ink)]'
              : 'border-[var(--color-rule-2)] text-[var(--color-ink-2)] hover:bg-[var(--color-paper-2)]'}`}
          >
            {filter.label}{filter.value === 'held' && meta.held > 0 ? ` (${meta.held})` : ''}
          </button>
        ))}
      </div>

      {loading ? (
        <div className="grid place-items-center py-20"><Spinner size="lg" /></div>
      ) : enquiries.length === 0 ? (
        <Card className="p-12 text-center">
          <ChatText className="mx-auto h-9 w-9 text-[var(--color-muted)]" />
          <h2 className="mt-4 text-lg font-semibold">Nothing here yet</h2>
          <p className="mt-2 text-sm text-[var(--color-muted)]">New submissions from the website forms will appear here.</p>
        </Card>
      ) : (
        <Card>
          <div className="border-b border-[var(--color-rule-2)] px-6 py-4 text-sm text-[var(--color-muted)]">
            {meta.total} {meta.total === 1 ? 'enquiry' : 'enquiries'} received
          </div>
          <Table>
            <TableHeader><TableRow>
              <TableHeadCell>From</TableHeadCell>
              <TableHeadCell>Type</TableHeadCell>
              <TableHeadCell>Details</TableHeadCell>
              <TableHeadCell>Received</TableHeadCell>
              <TableHeadCell>Email delivery</TableHeadCell>
            </TableRow></TableHeader>
            <TableBody>{enquiries.map((enquiry) => (
              <TableRow key={enquiry.id}>
                <TableCell className="min-w-56 align-top">
                  <div className="font-semibold text-[var(--color-ink)]">{enquiry.name}</div>
                  {enquiry.organisation && <div className="mt-1 text-xs text-[var(--color-ink-2)]">{enquiry.organisation}</div>}
                  <a className="mt-1 block text-xs text-[var(--color-muted)] hover:text-[var(--color-ink)]" href={`mailto:${enquiry.email}`}>{enquiry.email}</a>
                  {enquiry.phone && <a className="mt-1 block text-xs text-[var(--color-muted)] hover:text-[var(--color-ink)]" href={`tel:${enquiry.phone}`}>{enquiry.phone}</a>}
                </TableCell>
                <TableCell className="align-top font-medium">{enquiry.type_label}</TableCell>
                <TableCell className="min-w-72 align-top text-xs leading-5">
                  <dl className="space-y-1">
                    {Object.entries(enquiry.details || {}).map(([key, value]) => (
                      <div key={key}>
                        <dt className="inline font-medium text-[var(--color-ink-2)]">{label(key)}: </dt>
                        <dd className="inline text-[var(--color-muted)]">{Array.isArray(value) ? value.join(', ') : String(value)}</dd>
                      </div>
                    ))}
                  </dl>
                  {enquiry.message && <p className="mt-2 max-w-xl whitespace-pre-wrap text-[var(--color-muted)]">{enquiry.message}</p>}
                </TableCell>
                <TableCell className="min-w-44 align-top">{dateTime(enquiry.created_at)}</TableCell>
                <TableCell className="align-top">
                  {enquiry.delivery_status === 'spam' ? (
                    <Badge variant="draft">Held as spam</Badge>
                  ) : (
                    <Badge variant={enquiry.delivery_status === 'sent' ? 'published' : 'danger'}>
                      {enquiry.delivery_status === 'sent' ? 'Sent' : 'Not emailed'}
                    </Badge>
                  )}
                  {enquiry.spam_reason && (
                    <p className="mt-2 max-w-48 text-xs leading-4 text-[var(--color-muted)]">
                      {enquiry.delivery_status === 'spam' ? '' : 'Check: '}{enquiry.spam_reason}
                    </p>
                  )}
                  {enquiry.delivery_status === 'spam' && (
                    <p className="mt-1 max-w-48 text-xs leading-4 text-[var(--color-muted)]">Deleted automatically after 30 days.</p>
                  )}
                  {enquiry.delivery_status === 'spam' && (
                    <Button className="mt-2" variant="secondary" size="sm" isLoading={releasing === enquiry.id} onClick={() => release(enquiry)}>
                      Not spam, send it
                    </Button>
                  )}
                </TableCell>
              </TableRow>
            ))}</TableBody>
          </Table>
          {meta.last_page > 1 && <Pagination currentPage={meta.current_page} totalPages={meta.last_page} onPageChange={load} />}
        </Card>
      )}
    </div>
  );
}

import { useEffect, useState } from 'react';
import { Briefcase, PencilSimple, Plus, Trash } from '@phosphor-icons/react';
import toast from 'react-hot-toast';
import { jobAPI, apiErrorMessage } from '../lib/api';
import Button from '../components/ui/Button';
import Input from '../components/ui/Input';
import Badge from '../components/ui/Badge';
import Spinner from '../components/ui/Spinner';
import Modal, { ModalContent, ModalFooter, ModalHeader, ModalTitle } from '../components/ui/Modal';
import { Card } from '../components/ui/Card';
import { Table, TableBody, TableCell, TableHeadCell, TableHeader, TableRow } from '../components/ui/Table';

const emptyJob = { title: '', slug: '', location: '', job_type: 'Full-time', hours: '', salary: '', employment_type: 'Permanent', summary: '', description: '', responsibilities: '', essential: '', desirable: '', benefits: '', application_email: '', status: 'draft', published_at: '', closes_at: '' };
const listFields = ['responsibilities', 'essential', 'desirable', 'benefits'];
const toLines = (items) => (items || []).join('\n');
const fromLines = (value) => value.split('\n').map((line) => line.trim()).filter(Boolean);
const slugify = (value) => value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');

export default function Jobs() {
  const [jobs, setJobs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState(null);
  const [form, setForm] = useState(emptyJob);
  const [saving, setSaving] = useState(false);
  const [deleting, setDeleting] = useState(null);

  const load = async () => {
    try { setLoading(true); setJobs((await jobAPI.list()).data.data || []); }
    catch (error) { toast.error(apiErrorMessage(error, 'Could not load jobs')); }
    finally { setLoading(false); }
  };
  useEffect(() => { load(); }, []);

  const openEditor = (job = null) => {
    setEditing(job || { id: null });
    setForm(job ? { ...job, ...Object.fromEntries(listFields.map((field) => [field, toLines(job[field])])), published_at: job.published_at?.slice(0, 16) || '', closes_at: job.closes_at || '' } : emptyJob);
  };

  const change = (event) => {
    const { name, value } = event.target;
    setForm((current) => ({ ...current, [name]: value, ...(name === 'title' && !editing?.id ? { slug: slugify(value) } : {}) }));
  };

  const save = async (event) => {
    event.preventDefault(); setSaving(true);
    const payload = { ...form, ...Object.fromEntries(listFields.map((field) => [field, fromLines(form[field])])), published_at: form.published_at || null, closes_at: form.closes_at || null, application_email: form.application_email || null };
    try {
      if (editing.id) await jobAPI.update(editing.id, payload); else await jobAPI.create(payload);
      toast.success(editing.id ? 'Job updated' : 'Job created'); setEditing(null); await load();
    } catch (error) { toast.error(apiErrorMessage(error, 'Could not save job')); }
    finally { setSaving(false); }
  };

  const remove = async () => {
    try { await jobAPI.delete(deleting.id); toast.success('Job deleted'); setDeleting(null); await load(); }
    catch (error) { toast.error(apiErrorMessage(error, 'Could not delete job')); }
  };

  return <div className="space-y-6">
    <div className="flex flex-wrap items-end justify-between gap-4"><div><h1 className="page-heading">Jobs</h1><p className="page-deck">Create vacancies, review their status, then publish them to the Careers page.</p></div><Button onClick={() => openEditor()}><Plus className="h-4 w-4" weight="bold"/>New job</Button></div>
    {loading ? <div className="grid place-items-center py-20"><Spinner size="lg"/></div> : jobs.length === 0 ? <Card className="p-12 text-center"><Briefcase className="mx-auto h-9 w-9 text-[var(--color-muted)]"/><h2 className="mt-4 text-lg font-semibold">No job posts yet</h2><p className="mt-2 text-sm text-[var(--color-muted)]">Create a role and keep it as a draft until it is ready.</p></Card> : <Card><Table><TableHeader><TableRow><TableHeadCell>Role</TableHeadCell><TableHeadCell>Location</TableHeadCell><TableHeadCell>Status</TableHeadCell><TableHeadCell>Closing date</TableHeadCell><TableHeadCell className="text-right">Actions</TableHeadCell></TableRow></TableHeader><TableBody>{jobs.map((job) => <TableRow key={job.id}><TableCell><button className="font-semibold hover:underline" onClick={() => openEditor(job)}>{job.title}</button><div className="mt-1 text-xs text-[var(--color-muted)]">/careers/{job.slug}/</div></TableCell><TableCell>{job.location || '—'}</TableCell><TableCell><Badge variant={job.status === 'published' ? 'published' : job.status === 'closed' ? 'danger' : 'draft'}>{job.status}</Badge></TableCell><TableCell>{job.closes_at || 'Open until filled'}</TableCell><TableCell><div className="flex justify-end gap-2"><Button variant="ghost" size="sm" aria-label={`Edit ${job.title}`} onClick={() => openEditor(job)}><PencilSimple className="h-4 w-4"/></Button><Button variant="danger" size="sm" aria-label={`Delete ${job.title}`} onClick={() => setDeleting(job)}><Trash className="h-4 w-4"/></Button></div></TableCell></TableRow>)}</TableBody></Table></Card>}

    <Modal isOpen={!!editing} onClose={() => setEditing(null)} className="w-full max-w-4xl"><form onSubmit={save}><ModalHeader onClose={() => setEditing(null)}><ModalTitle>{editing?.id ? 'Edit job' : 'New job'}</ModalTitle></ModalHeader><ModalContent><div className="grid gap-5 sm:grid-cols-2"><Input required label="Job title" name="title" value={form.title} onChange={change}/><Input required label="URL slug" name="slug" value={form.slug} onChange={change}/><Input label="Location" name="location" value={form.location} onChange={change}/><Input label="Salary" name="salary" value={form.salary} onChange={change}/><Input label="Job type" name="job_type" value={form.job_type} onChange={change}/><Input label="Hours" name="hours" value={form.hours} onChange={change}/><Input label="Employment type" name="employment_type" value={form.employment_type} onChange={change}/><Input type="email" label="Application email" name="application_email" value={form.application_email} onChange={change} placeholder="Uses site contact email when blank"/><label className="flex flex-col text-sm font-medium">Status<select className="field-control mt-1.5" name="status" value={form.status} onChange={change}><option value="draft">Draft</option><option value="published">Published</option><option value="closed">Closed</option></select></label><Input type="date" label="Closing date" name="closes_at" value={form.closes_at} onChange={change}/></div><TextArea required label="Summary" name="summary" value={form.summary} onChange={change}/><TextArea label="The role" name="description" value={form.description} onChange={change}/>{[['responsibilities','Key responsibilities'],['essential','Essential skills and experience'],['desirable','Desirable'],['benefits','What we offer']].map(([name,label]) => <TextArea key={name} label={label} help="One item per line" name={name} value={form[name]} onChange={change}/>)}</ModalContent><ModalFooter><Button type="button" variant="secondary" onClick={() => setEditing(null)}>Cancel</Button><Button type="submit" isLoading={saving}>Save job</Button></ModalFooter></form></Modal>
    <Modal isOpen={!!deleting} onClose={() => setDeleting(null)} className="w-full max-w-md"><ModalHeader onClose={() => setDeleting(null)}><ModalTitle>Delete job</ModalTitle></ModalHeader><ModalContent><p>Delete <strong>{deleting?.title}</strong>? It will be removed from the Careers page after the next publish.</p></ModalContent><ModalFooter><Button variant="secondary" onClick={() => setDeleting(null)}>Cancel</Button><Button variant="danger" onClick={remove}>Delete</Button></ModalFooter></Modal>
  </div>;
}

function TextArea({ label, help, ...props }) {
  return <label className="mt-5 flex flex-col text-sm font-medium">{label}<textarea className="field-control mt-1.5 min-h-28" {...props}/>{help && <span className="mt-1 text-xs font-normal text-[var(--color-muted)]">{help}</span>}</label>;
}

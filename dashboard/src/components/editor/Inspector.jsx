import { useMemo, useState } from 'react';
import { motion as Motion } from 'motion/react';
import {
  Plus, Trash, CaretUp, CaretDown, Eye, EyeSlash,
} from '../../lib/icons';
import Badge from '../ui/Badge';
import SchemaFields, { Field, inputCls } from './SchemaFields';
import { getPath, setPath, shownInPanel } from '../../lib/editorSchema';

/**
 * Right-hand control panel. Three tabs:
 *  - Page:    page-level settings + the ordered list of sections
 *  - Section: structural controls for the selected section
 *  - SEO:     search / social metadata
 */
export default function Inspector({
  page, selected, sectionTypes,
  updatePage, updateSeo, updateSection, updateData,
  addSection, removeSection, moveSection, selectSection,
}) {
  const [tab, setTab] = useState('page');
  const [addOpen, setAddOpen] = useState(false);
  // The types this page may hold, from the server: null until they arrive.
  const types = useMemo(() => new Map((sectionTypes || []).map((type) => [type.type, type])), [sectionTypes]);
  const labelOf = (type) => types.get(type)?.label || type;

  const tabBtn = (id, text) => (
    <button
      onClick={() => setTab(id)}
      role="tab"
      aria-selected={tab === id}
      className={`relative flex-1 px-3 py-2.5 text-sm font-medium border-b-2 border-transparent transition-colors duration-200 ${
        tab === id ? 'text-[var(--color-ink)]' : 'text-[var(--color-muted)] hover:text-[var(--color-ink-2)]'
      }`}
    >
      {text}
      {/* One underline, which slides to the chosen tab. */}
      {tab === id && (
        <Motion.span
          layoutId="inspector-tab"
          className="absolute inset-x-0 -bottom-0.5 h-0.5 bg-[var(--color-ink)]"
          transition={{ type: 'spring', stiffness: 460, damping: 40 }}
        />
      )}
    </button>
  );

  return (
    <aside className="flex h-full w-[340px] shrink-0 flex-col border-l border-[var(--color-rule-2)] bg-[var(--color-paper)] max-[767px]:fixed max-[767px]:inset-x-0 max-[767px]:bottom-0 max-[767px]:z-30 max-[767px]:h-[46dvh] max-[767px]:w-full max-[767px]:border-l-0 max-[767px]:border-t">
      <div className="flex border-b border-[var(--color-rule-2)]" role="tablist" aria-label="Editor controls">
        {tabBtn('page', 'Page')}
        {tabBtn('section', 'Section')}
        {tabBtn('seo', 'SEO')}
      </div>

      <div className="flex-1 overflow-y-auto p-4">
        {tab === 'page' && (
          <PageTab
            page={page}
            selected={selected}
            updatePage={updatePage}
            addSection={addSection}
            removeSection={removeSection}
            moveSection={moveSection}
            selectSection={selectSection}
            updateSection={updateSection}
            addOpen={addOpen}
            setAddOpen={setAddOpen}
            sectionTypes={sectionTypes}
            labelOf={labelOf}
          />
        )}
        {tab === 'section' && (
          <SectionTab
            selected={selected}
            schema={selected ? types.get(selected.type) : null}
            loaded={sectionTypes != null}
            labelOf={labelOf}
            updateSection={updateSection}
            updateData={updateData}
            goToPage={() => setTab('page')}
          />
        )}
        {tab === 'seo' && <SeoTab seo={page.seo || {}} updateSeo={updateSeo} />}
      </div>
    </aside>
  );
}

function PageTab({
  page, selected, updatePage, addSection, removeSection, moveSection,
  selectSection, updateSection, addOpen, setAddOpen, sectionTypes, labelOf,
}) {
  return (
    <div className="space-y-5">
      <div className="space-y-3">
        <Field label="Title">
          <input className={inputCls} value={page.title || ''} onChange={(e) => updatePage({ title: e.target.value })} />
        </Field>
        <Field label="Slug">
          <input className={inputCls} value={page.slug || ''} onChange={(e) => updatePage({ slug: e.target.value })} />
        </Field>
        <div className="grid grid-cols-2 gap-3">
          <Field label="Status">
            <select className={inputCls} value={page.status || 'draft'} onChange={(e) => updatePage({ status: e.target.value })}>
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
          </Field>
          <Field label="Template">
            <input className={inputCls} value={page.template || ''} onChange={(e) => updatePage({ template: e.target.value })} placeholder="default" />
          </Field>
        </div>
        <label className="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
          <input type="checkbox" className="rounded border-[var(--color-rule)]" checked={!!page.is_homepage} onChange={(e) => updatePage({ is_homepage: e.target.checked })} />
          Set as homepage
        </label>

        {/* Site navigation is derived from the page tree, so these two fields
            are the nav editor — there is no separate menu builder. */}
        <label className="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
          <input
            type="checkbox"
            className="rounded border-[var(--color-rule)]"
            checked={page.show_in_nav !== false}
            onChange={(e) => updatePage({ show_in_nav: e.target.checked })}
          />
          Show in site navigation
        </label>

        {page.show_in_nav !== false && !page.is_homepage && (
          <Field label="Navigation label">
            <input
              className={inputCls}
              value={page.nav_label || ''}
              onChange={(e) => updatePage({ nav_label: e.target.value })}
              placeholder={page.title || 'Defaults to the page title'}
            />
          </Field>
        )}
        <Field label="Excerpt">
          <textarea className={`${inputCls} h-20 resize-none`} value={page.excerpt || ''} onChange={(e) => updatePage({ excerpt: e.target.value })} />
        </Field>
      </div>

      <div>
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-xs font-semibold uppercase tracking-wide text-[var(--color-muted)]">Sections</h3>
          <div className="relative">
            <button
              type="button"
              onClick={() => setAddOpen((o) => !o)}
              className="inline-flex items-center gap-1 text-sm text-[var(--color-ink)] font-medium"
            >
              <Plus className="h-4 w-4" weight="bold" /> Add
            </button>
            {addOpen && (
              <>
                <div className="fixed inset-0 z-10" onClick={() => setAddOpen(false)} />
                <div className="absolute right-0 z-20 mt-1 w-56 rounded-lg border border-[var(--color-rule-2)] bg-[var(--color-paper)] shadow-lg py-1 max-h-72 overflow-y-auto">
                  {sectionTypes == null && (
                    <p className="px-3 py-2 text-sm text-[var(--color-muted)]">Loading section types…</p>
                  )}
                  {(sectionTypes || []).map((type) => (
                    <button
                      key={type.type}
                      onClick={() => { addSection(type.type); setAddOpen(false); }}
                      className="w-full text-left px-3 py-2 hover:bg-[var(--color-paper-2)]"
                    >
                      <div className="text-sm font-medium text-[var(--color-ink)]">{type.label}</div>
                      <div className="text-xs text-[var(--color-muted)]">{type.blurb}</div>
                    </button>
                  ))}
                </div>
              </>
            )}
          </div>
        </div>

        {(page.sections || []).length === 0 && (
          <p className="text-sm text-[var(--color-muted)] py-4 text-center">No sections yet. Add one to begin.</p>
        )}

        <ul className="space-y-1.5">
          {(page.sections || []).map((s, i) => {
            const isSel = selected && s._uid === selected._uid;
            return (
              <Motion.li
                key={s._uid}
                // Moving a section up or down slides it to its new place.
                layout="position"
                transition={{ type: 'spring', stiffness: 480, damping: 42 }}
                className={`group relative flex items-center rounded-lg border px-2.5 py-2 cursor-pointer ${
                  isSel ? 'border-[var(--color-ink)] bg-[var(--color-paper-3)]' : 'border-[var(--color-rule-2)] hover:border-[var(--color-rule)]'
                }`}
              >
                <button
                  type="button"
                  onClick={() => selectSection(s._uid)}
                  aria-pressed={!!isSel}
                  className="flex min-h-11 min-w-0 flex-1 items-center gap-2 text-left"
                >
                  <span className="w-5 shrink-0 text-center text-xs font-medium text-[var(--color-muted)]">{i + 1}</span>
                  <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-medium text-[var(--color-ink)]" title={s.title || labelOf(s.type)}>
                      {s.title || labelOf(s.type)}
                    </div>
                    <div className="text-xs text-[var(--color-muted)]">{labelOf(s.type)}</div>
                  </div>
                </button>
                {/* The row actions are hidden until the row is hovered or focused, so
                    they are laid over its right edge rather than holding 182px of a
                    340px panel open. The name then gets the whole row to itself. */}
                <div
                  className="pointer-events-none absolute inset-y-px right-px flex items-center gap-0.5 rounded-r-lg pl-8 pr-2 opacity-0 transition-opacity group-hover:pointer-events-auto group-hover:opacity-100 group-focus-within:pointer-events-auto group-focus-within:opacity-100"
                  style={{
                    backgroundImage: `linear-gradient(to right, transparent, ${
                      isSel ? 'var(--color-paper-3)' : 'var(--color-paper)'
                    } 2rem)`,
                  }}
                >
                  <IconBtn title={s.is_visible ? 'Hide' : 'Show'} onClick={(e) => { e.stopPropagation(); updateSection(s._uid, { is_visible: !s.is_visible }); }}>
                    {s.is_visible ? <Eye className="h-4 w-4" /> : <EyeSlash className="h-4 w-4 text-[var(--color-muted)]" />}
                  </IconBtn>
                  <IconBtn title="Move up" disabled={i === 0} onClick={(e) => { e.stopPropagation(); moveSection(s._uid, -1); }}>
                    <CaretUp className="h-4 w-4" weight="bold" />
                  </IconBtn>
                  <IconBtn title="Move down" disabled={i === page.sections.length - 1} onClick={(e) => { e.stopPropagation(); moveSection(s._uid, 1); }}>
                    <CaretDown className="h-4 w-4" weight="bold" />
                  </IconBtn>
                  <IconBtn title="Delete" onClick={(e) => { e.stopPropagation(); removeSection(s._uid); }}>
                    <Trash className="h-4 w-4 text-[var(--color-ink)]" weight="bold" />
                  </IconBtn>
                </div>
              </Motion.li>
            );
          })}
        </ul>
      </div>
    </div>
  );
}

function SectionTab({ selected, schema, loaded, labelOf, updateSection, updateData, goToPage }) {
  if (!selected) {
    return (
      <div className="text-center py-10">
        <p className="text-sm text-[var(--color-muted)]">No section selected.</p>
        <button onClick={goToPage} className="mt-2 text-sm text-[var(--color-ink)]">
          Choose one from the Page tab
        </button>
      </div>
    );
  }

  const read = (path) => getPath(selected, path);
  // title and content sit on the section; everything else is in its data.
  const write = (path, value) => {
    if (path === 'title' || path === 'content') {
      updateSection(selected._uid, { [path]: value });
    } else {
      updateData(selected._uid, setPath(selected.data || {}, path.replace(/^data\./, ''), value));
    }
  };
  const hasPanelFields = schema && Object.values(schema.fields).some(shownInPanel);

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <Badge variant="primary">{labelOf(selected.type)}</Badge>
        <label className="flex items-center gap-2 text-sm text-[var(--color-muted)]">
          <input type="checkbox" className="rounded border-[var(--color-rule)]" checked={!!selected.is_visible} onChange={(e) => updateSection(selected._uid, { is_visible: e.target.checked })} />
          Visible
        </label>
      </div>

      <p className="text-xs text-[var(--color-muted)] -mt-2">
        Text is edited directly on the preview. Structural options are here.
      </p>

      {schema && <SchemaFields fields={schema.fields} read={read} write={write} />}

      {schema && !hasPanelFields && (
        <p className="text-sm text-[var(--color-muted)]">This section has no extra options — edit its text on the preview.</p>
      )}
      {!schema && loaded && (
        <p className="text-sm text-[var(--color-muted)]">
          The theme that draws this page does not provide this type of section, so it cannot be saved here. Remove it, or switch the page back to a template that has it.
        </p>
      )}
    </div>
  );
}

function SeoTab({ seo, updateSeo }) {
  const f = (key) => (e) => updateSeo({ [key]: e.target.value });
  return (
    <div className="space-y-3">
      <Field label="Meta title">
        <input className={inputCls} value={seo.meta_title || ''} onChange={f('meta_title')} />
      </Field>
      <Field label="Meta description">
        <textarea className={`${inputCls} h-20 resize-none`} value={seo.meta_description || ''} onChange={f('meta_description')} />
      </Field>
      <Field label="Meta keywords">
        <input className={inputCls} value={seo.meta_keywords || ''} onChange={f('meta_keywords')} placeholder="comma, separated" />
      </Field>
      <Field label="OG title">
        <input className={inputCls} value={seo.og_title || ''} onChange={f('og_title')} />
      </Field>
      <Field label="OG description">
        <textarea className={`${inputCls} h-16 resize-none`} value={seo.og_description || ''} onChange={f('og_description')} />
      </Field>
      <Field label="OG image URL">
        <input className={inputCls} value={seo.og_image || ''} onChange={f('og_image')} placeholder="https://…" />
      </Field>
      <Field label="Robots">
        <select className={inputCls} value={seo.robots || 'index, follow'} onChange={f('robots')}>
          <option>index, follow</option>
          <option>noindex, follow</option>
          <option>index, nofollow</option>
          <option>noindex, nofollow</option>
        </select>
      </Field>
    </div>
  );
}

function IconBtn({ children, onClick, title, disabled }) {
  return (
    <button
      onClick={onClick}
      title={title}
      aria-label={title}
      disabled={disabled}
      className="min-h-11 min-w-11 rounded hover:bg-[var(--color-paper)] text-[var(--color-muted)] disabled:opacity-30 disabled:hover:bg-transparent"
    >
      {children}
    </button>
  );
}

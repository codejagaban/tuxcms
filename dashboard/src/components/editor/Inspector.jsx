import { useState } from 'react';
import {
  Plus, Trash2, ChevronUp, ChevronDown, Eye, EyeOff, X,
} from 'lucide-react';
import Input from '../ui/Input';
import Badge from '../ui/Badge';
import {
  SECTION_REGISTRY, SECTION_MENU, sectionLabel, setPath,
} from '../../lib/editorSchema';

const Field = ({ label, children }) => (
  <label className="block">
    <span className="mb-1 block text-xs font-medium text-gray-600">{label}</span>
    {children}
  </label>
);

const inputCls =
  'w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500';

/**
 * Right-hand control panel. Three tabs:
 *  - Page:    page-level settings + the ordered list of sections
 *  - Section: structural controls for the selected section
 *  - SEO:     search / social metadata
 */
export default function Inspector({
  page, selected, forms,
  updatePage, updateSeo, updateSection, updateData,
  addSection, removeSection, moveSection, selectSection,
}) {
  const [tab, setTab] = useState('page');
  const [addOpen, setAddOpen] = useState(false);

  const tabBtn = (id, text) => (
    <button
      onClick={() => setTab(id)}
      className={`flex-1 px-3 py-2.5 text-sm font-medium border-b-2 transition-colors ${
        tab === id
          ? 'border-blue-600 text-blue-600'
          : 'border-transparent text-gray-500 hover:text-gray-700'
      }`}
    >
      {text}
    </button>
  );

  return (
    <aside className="w-[340px] shrink-0 border-l border-gray-200 bg-white flex flex-col h-full">
      <div className="flex border-b border-gray-200">
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
          />
        )}
        {tab === 'section' && (
          <SectionTab
            selected={selected}
            forms={forms}
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
  selectSection, updateSection, addOpen, setAddOpen,
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
        <label className="flex items-center gap-2 text-sm text-gray-700">
          <input type="checkbox" className="rounded border-gray-300" checked={!!page.is_homepage} onChange={(e) => updatePage({ is_homepage: e.target.checked })} />
          Set as homepage
        </label>
        <Field label="Excerpt">
          <textarea className={`${inputCls} h-20 resize-none`} value={page.excerpt || ''} onChange={(e) => updatePage({ excerpt: e.target.value })} />
        </Field>
      </div>

      <div>
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-500">Sections</h3>
          <div className="relative">
            <button
              onClick={() => setAddOpen((o) => !o)}
              className="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium"
            >
              <Plus className="h-4 w-4" /> Add
            </button>
            {addOpen && (
              <>
                <div className="fixed inset-0 z-10" onClick={() => setAddOpen(false)} />
                <div className="absolute right-0 z-20 mt-1 w-56 rounded-lg border border-gray-200 bg-white shadow-lg py-1 max-h-72 overflow-y-auto">
                  {SECTION_MENU.map((type) => (
                    <button
                      key={type}
                      onClick={() => { addSection(type); setAddOpen(false); }}
                      className="w-full text-left px-3 py-2 hover:bg-gray-50"
                    >
                      <div className="text-sm font-medium text-gray-900">{SECTION_REGISTRY[type].label}</div>
                      <div className="text-xs text-gray-500">{SECTION_REGISTRY[type].blurb}</div>
                    </button>
                  ))}
                </div>
              </>
            )}
          </div>
        </div>

        {(page.sections || []).length === 0 && (
          <p className="text-sm text-gray-400 py-4 text-center">No sections yet. Add one to begin.</p>
        )}

        <ul className="space-y-1.5">
          {(page.sections || []).map((s, i) => {
            const isSel = selected && s._uid === selected._uid;
            return (
              <li
                key={s._uid}
                onClick={() => selectSection(s._uid)}
                className={`group flex items-center gap-2 rounded-lg border px-2.5 py-2 cursor-pointer ${
                  isSel ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <span className="w-5 shrink-0 text-center text-xs font-medium text-gray-300">{i + 1}</span>
                <div className="flex-1 min-w-0">
                  <div className="text-sm font-medium text-gray-900 truncate">
                    {s.title || sectionLabel(s.type)}
                  </div>
                  <div className="text-xs text-gray-400">{sectionLabel(s.type)}</div>
                </div>
                <div className="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                  <IconBtn title={s.is_visible ? 'Hide' : 'Show'} onClick={(e) => { e.stopPropagation(); updateSection(s._uid, { is_visible: !s.is_visible }); }}>
                    {s.is_visible ? <Eye className="h-4 w-4" /> : <EyeOff className="h-4 w-4 text-gray-400" />}
                  </IconBtn>
                  <IconBtn title="Move up" disabled={i === 0} onClick={(e) => { e.stopPropagation(); moveSection(s._uid, -1); }}>
                    <ChevronUp className="h-4 w-4" />
                  </IconBtn>
                  <IconBtn title="Move down" disabled={i === page.sections.length - 1} onClick={(e) => { e.stopPropagation(); moveSection(s._uid, 1); }}>
                    <ChevronDown className="h-4 w-4" />
                  </IconBtn>
                  <IconBtn title="Delete" onClick={(e) => { e.stopPropagation(); removeSection(s._uid); }}>
                    <Trash2 className="h-4 w-4 text-red-500" />
                  </IconBtn>
                </div>
              </li>
            );
          })}
        </ul>
      </div>
    </div>
  );
}

function SectionTab({ selected, forms, updateSection, updateData, goToPage }) {
  if (!selected) {
    return (
      <div className="text-center py-10">
        <p className="text-sm text-gray-500">No section selected.</p>
        <button onClick={goToPage} className="mt-2 text-sm text-blue-600 hover:text-blue-700">
          Choose one from the Page tab
        </button>
      </div>
    );
  }

  const d = selected.data || {};
  const reg = SECTION_REGISTRY[selected.type];
  const setData = (path, value) => updateData(selected._uid, setPath(d, path, value));

  const listCfg = reg?.list;
  const listItems = listCfg ? (d[listCfg.path] || []) : null;

  const addItem = () => {
    updateData(selected._uid, { ...d, [listCfg.path]: [...(d[listCfg.path] || []), listCfg.item()] });
  };
  const removeItem = (idx) => {
    updateData(selected._uid, { ...d, [listCfg.path]: (d[listCfg.path] || []).filter((_, i) => i !== idx) });
  };
  const setItemProp = (idx, key, value) => {
    const list = [...(d[listCfg.path] || [])];
    list[idx] = { ...list[idx], [key]: value };
    updateData(selected._uid, { ...d, [listCfg.path]: list });
  };

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <Badge variant="primary">{sectionLabel(selected.type)}</Badge>
        <label className="flex items-center gap-2 text-sm text-gray-600">
          <input type="checkbox" className="rounded border-gray-300" checked={!!selected.is_visible} onChange={(e) => updateSection(selected._uid, { is_visible: e.target.checked })} />
          Visible
        </label>
      </div>

      <p className="text-xs text-gray-500 -mt-2">
        Text is edited directly on the preview. Structural options are here.
      </p>

      {/* Hero controls */}
      {selected.type === 'hero' && (
        <>
          <Field label="Alignment">
            <select className={inputCls} value={d.alignment || 'center'} onChange={(e) => setData('alignment', e.target.value)}>
              <option value="left">Left</option>
              <option value="center">Center</option>
            </select>
          </Field>
          <Field label="Background image URL">
            <input className={inputCls} value={d.background_image || ''} onChange={(e) => setData('background_image', e.target.value)} placeholder="https://…" />
          </Field>
          <Field label="Primary button link">
            <input className={inputCls} value={d.primary_cta?.url || ''} onChange={(e) => setData('primary_cta.url', e.target.value)} placeholder="/contact" />
          </Field>
          <Field label="Secondary button link">
            <input className={inputCls} value={d.secondary_cta?.url || ''} onChange={(e) => setData('secondary_cta.url', e.target.value)} placeholder="/about" />
          </Field>
        </>
      )}

      {selected.type === 'cta' && (
        <Field label="Button link">
          <input className={inputCls} value={d.primary_cta?.url || ''} onChange={(e) => setData('primary_cta.url', e.target.value)} placeholder="/contact" />
        </Field>
      )}

      {selected.type === 'features' && (
        <Field label="Columns">
          <select className={inputCls} value={String(d.columns || 3)} onChange={(e) => setData('columns', Number(e.target.value))}>
            <option value="2">2 columns</option>
            <option value="3">3 columns</option>
          </select>
        </Field>
      )}

      {selected.type === 'form' && (
        <Field label="Form to embed">
          <select
            className={inputCls}
            value={d.form_id || ''}
            onChange={(e) => {
              const form = forms.find((f) => String(f.id) === e.target.value);
              updateData(selected._uid, { ...d, form_id: form ? form.id : null, form_slug: form ? form.slug : null });
            }}
          >
            <option value="">Select a form…</option>
            {forms.map((f) => (
              <option key={f.id} value={f.id}>{f.name}</option>
            ))}
          </select>
        </Field>
      )}

      {/* Repeatable list management */}
      {listCfg && (
        <div>
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-500">
              {listCfg.label} items ({listItems.length})
            </h3>
            <button onClick={addItem} className="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium">
              <Plus className="h-4 w-4" /> Add
            </button>
          </div>
          <ul className="space-y-1.5">
            {listItems.map((item, idx) => (
              <li key={idx} className="flex items-center gap-2 rounded-lg border border-gray-200 px-2.5 py-2">
                <span className="text-xs text-gray-400 w-5">{idx + 1}</span>
                <span className="flex-1 text-sm text-gray-700 truncate">
                  {item.title || item.question || item.author || item.name || item.label || `${listCfg.label} ${idx + 1}`}
                </span>
                {selected.type === 'features' && (
                  <input
                    className="w-20 px-2 py-1 text-xs border border-gray-200 rounded"
                    value={item.icon || ''}
                    onChange={(e) => setItemProp(idx, 'icon', e.target.value)}
                    placeholder="icon"
                    title="lucide icon name"
                  />
                )}
                <button onClick={() => removeItem(idx)} title="Remove" className="text-gray-400 hover:text-red-500">
                  <X className="h-4 w-4" />
                </button>
              </li>
            ))}
          </ul>
          {selected.type === 'features' && (
            <p className="mt-2 text-xs text-gray-400">
              Icon names: sparkles, zap, shield, star, compass, code, layers, pen-tool, gauge, users.
            </p>
          )}
        </div>
      )}

      {!listCfg && !['hero', 'cta', 'form', 'features'].includes(selected.type) && (
        <p className="text-sm text-gray-400">This section has no extra options — edit its text on the preview.</p>
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
      disabled={disabled}
      className="p-1 rounded hover:bg-white text-gray-500 disabled:opacity-30 disabled:hover:bg-transparent"
    >
      {children}
    </button>
  );
}

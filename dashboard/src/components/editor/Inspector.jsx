import { useState } from 'react';
import {
  Plus, Trash, CaretUp, CaretDown, Eye, EyeSlash, X,
} from '@phosphor-icons/react';
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

const inputCls = 'field-control text-sm';

/**
 * Right-hand control panel. Three tabs:
 *  - Page:    page-level settings + the ordered list of sections
 *  - Section: structural controls for the selected section
 *  - SEO:     search / social metadata
 */
export default function Inspector({
  page, selected,
  updatePage, updateSeo, updateSection, updateData,
  addSection, removeSection, moveSection, selectSection,
}) {
  const [tab, setTab] = useState('page');
  const [addOpen, setAddOpen] = useState(false);

  const tabBtn = (id, text) => (
    <button
      onClick={() => setTab(id)}
      role="tab"
      aria-selected={tab === id}
      className={`flex-1 px-3 py-2.5 text-sm font-medium border-b-2 transition-colors ${
        tab === id
          ? 'border-black text-black'
          : 'border-transparent text-gray-500 hover:text-gray-700'
      }`}
    >
      {text}
    </button>
  );

  return (
    <aside className="flex h-full w-[340px] shrink-0 flex-col border-l border-[var(--color-rule-2)] bg-[var(--color-paper)] max-[767px]:fixed max-[767px]:inset-x-0 max-[767px]:bottom-0 max-[767px]:z-30 max-[767px]:h-[46dvh] max-[767px]:w-full max-[767px]:border-l-0 max-[767px]:border-t">
      <div className="flex border-b border-gray-200" role="tablist" aria-label="Editor controls">
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

        {/* Site navigation is derived from the page tree, so these two fields
            are the nav editor — there is no separate menu builder. */}
        <label className="flex items-center gap-2 text-sm text-gray-700">
          <input
            type="checkbox"
            className="rounded border-gray-300"
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
          <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-500">Sections</h3>
          <div className="relative">
            <button
              type="button"
              onClick={() => setAddOpen((o) => !o)}
              className="inline-flex items-center gap-1 text-sm text-black hover:text-black font-medium"
            >
              <Plus className="h-4 w-4" weight="bold" /> Add
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
                className={`group flex items-center gap-2 rounded-lg border px-2.5 py-2 cursor-pointer ${
                  isSel ? 'border-black bg-neutral-100' : 'border-gray-200 hover:border-gray-300'
                }`}
              >
                <button
                  type="button"
                  onClick={() => selectSection(s._uid)}
                  aria-pressed={!!isSel}
                  className="flex min-h-11 min-w-0 flex-1 items-center gap-2 text-left"
                >
                  <span className="w-5 shrink-0 text-center text-xs font-medium text-gray-600">{i + 1}</span>
                  <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-medium text-gray-900">
                      {s.title || sectionLabel(s.type)}
                    </div>
                    <div className="text-xs text-gray-600">{sectionLabel(s.type)}</div>
                  </div>
                </button>
                <div className="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity">
                  <IconBtn title={s.is_visible ? 'Hide' : 'Show'} onClick={(e) => { e.stopPropagation(); updateSection(s._uid, { is_visible: !s.is_visible }); }}>
                    {s.is_visible ? <Eye className="h-4 w-4" /> : <EyeSlash className="h-4 w-4 text-gray-400" />}
                  </IconBtn>
                  <IconBtn title="Move up" disabled={i === 0} onClick={(e) => { e.stopPropagation(); moveSection(s._uid, -1); }}>
                    <CaretUp className="h-4 w-4" weight="bold" />
                  </IconBtn>
                  <IconBtn title="Move down" disabled={i === page.sections.length - 1} onClick={(e) => { e.stopPropagation(); moveSection(s._uid, 1); }}>
                    <CaretDown className="h-4 w-4" weight="bold" />
                  </IconBtn>
                  <IconBtn title="Delete" onClick={(e) => { e.stopPropagation(); removeSection(s._uid); }}>
                    <Trash className="h-4 w-4 text-black" weight="bold" />
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

function SectionTab({ selected, updateSection, updateData, goToPage }) {
  if (!selected) {
    return (
      <div className="text-center py-10">
        <p className="text-sm text-gray-500">No section selected.</p>
        <button onClick={goToPage} className="mt-2 text-sm text-black hover:text-black">
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
          <Field label="Feature image URL">
            <input className={inputCls} value={d.image || ''} onChange={(e) => setData('image', e.target.value)} placeholder="/themes/… or https://…" />
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

      {['text', 'contact'].includes(selected.type) && (
        <>
          <Field label="Eyebrow text">
            <input className={inputCls} value={d.eyebrow || ''} onChange={(e) => setData('eyebrow', e.target.value)} />
          </Field>
          <Field label="Image URL">
            <input className={inputCls} value={d.image || ''} onChange={(e) => setData('image', e.target.value)} placeholder="/themes/… or https://…" />
          </Field>
          <Field label="Image alt text">
            <input className={inputCls} value={d.image_alt || ''} onChange={(e) => setData('image_alt', e.target.value)} />
          </Field>
        </>
      )}

      {selected.type === 'contact_form' && (
        <>
          <Field label="Web3Forms access key">
            <input
              className={inputCls}
              value={d.access_key || ''}
              onChange={(e) => setData('access_key', e.target.value)}
              placeholder="Leave blank to use the site default"
            />
          </Field>
          <Field label="Email subject">
            <input className={inputCls} value={d.subject || ''} onChange={(e) => setData('subject', e.target.value)} placeholder="New enquiry" />
          </Field>
          <Field label="Button label">
            <input className={inputCls} value={d.button_label || ''} onChange={(e) => setData('button_label', e.target.value)} placeholder="Send message" />
          </Field>
          <Field label="Success message">
            <textarea className={`${inputCls} h-16 resize-none`} value={d.success_message || ''} onChange={(e) => setData('success_message', e.target.value)} />
          </Field>
        </>
      )}

      {selected.type === 'map' && (
        <>
          <Field label="Address query">
            <input className={inputCls} value={d.query || ''} onChange={(e) => setData('query', e.target.value)} placeholder="Street, city, postcode" />
          </Field>
          <div className="grid grid-cols-2 gap-3">
            <Field label="Latitude">
              <input className={inputCls} type="number" step="any" value={d.lat ?? ''} onChange={(e) => setData('lat', Number(e.target.value))} />
            </Field>
            <Field label="Longitude">
              <input className={inputCls} type="number" step="any" value={d.lng ?? ''} onChange={(e) => setData('lng', Number(e.target.value))} />
            </Field>
          </div>
          <Field label="Zoom">
            <input className={inputCls} type="number" min="1" max="20" value={d.zoom ?? 14} onChange={(e) => setData('zoom', Number(e.target.value))} />
          </Field>
          <Field label="Label">
            <input className={inputCls} value={d.label || ''} onChange={(e) => setData('label', e.target.value)} placeholder="Our office" />
          </Field>
        </>
      )}

      {/* Repeatable list management */}
      {listCfg && (
        <div>
          <div className="flex items-center justify-between mb-2">
            <h3 className="text-xs font-semibold uppercase tracking-wide text-gray-500">
              {listCfg.label} items ({listItems.length})
            </h3>
            <button onClick={addItem} className="inline-flex items-center gap-1 text-sm text-black hover:text-black font-medium">
              <Plus className="h-4 w-4" weight="bold" /> Add
            </button>
          </div>
          <ul className="space-y-1.5">
            {listItems.map((item, idx) => (
              <li key={idx} className="rounded-lg border border-gray-200 px-2.5 py-2">
                <div className="flex items-center gap-2">
                  <span className="text-xs text-gray-400 w-5">{idx + 1}</span>
                  <span className="flex-1 text-sm text-gray-700 truncate">
                    {item.title || item.question || item.author || item.name || item.label || `${listCfg.label} ${idx + 1}`}
                  </span>
                  <button onClick={() => removeItem(idx)} title="Remove" aria-label={`Remove ${listCfg.label} ${idx + 1}`} className="min-h-11 min-w-11 text-gray-400 hover:text-black">
                    <X className="h-4 w-4" />
                  </button>
                </div>
                {selected.type === 'features' && (
                  <div className="mt-2 grid gap-2">
                    <input className={inputCls} value={item.icon || ''} onChange={(e) => setItemProp(idx, 'icon', e.target.value)} placeholder="Icon name" aria-label={`${listCfg.label} ${idx + 1} icon name`} />
                    <input className={inputCls} value={item.image || ''} onChange={(e) => setItemProp(idx, 'image', e.target.value)} placeholder="Image URL" aria-label={`${listCfg.label} ${idx + 1} image URL`} />
                    <input className={inputCls} value={item.url || ''} onChange={(e) => setItemProp(idx, 'url', e.target.value)} placeholder="Booking or details URL" aria-label={`${listCfg.label} ${idx + 1} link URL`} />
                    <input className={inputCls} value={item.link_label || ''} onChange={(e) => setItemProp(idx, 'link_label', e.target.value)} placeholder="Link label" aria-label={`${listCfg.label} ${idx + 1} link label`} />
                  </div>
                )}
              </li>
            ))}
          </ul>
        </div>
      )}

      {!listCfg && !['hero', 'cta', 'contact_form', 'map', 'features'].includes(selected.type) && (
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
      aria-label={title}
      disabled={disabled}
      className="min-h-11 min-w-11 rounded hover:bg-white text-gray-500 disabled:opacity-30 disabled:hover:bg-transparent"
    >
      {children}
    </button>
  );
}

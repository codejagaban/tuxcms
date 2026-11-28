import { useState } from 'react';
import { Plus, CaretUp, CaretDown, X, ImageSquare } from '../../lib/icons';
import MediaPicker from './MediaPicker';
import { newValue, shownInPanel } from '../../lib/editorSchema';

// The side panel's controls for one section, drawn from its type's schema
// (see App\Support\ThemeManifest for the field types). Text that is edited on
// the preview is marked `inspector: false` and left out here.

export const inputCls = 'field-control text-sm';

export const Field = ({ label, children }) => (
  <label className="block">
    <span className="mb-1 block text-xs font-medium text-[var(--color-muted)]">{label}</span>
    {children}
  </label>
);

// The words a list item goes by in its row: its first naming field, else
// its first text, else its place in the list.
const summaryOf = (item, label, index) => {
  if (item && typeof item === 'object') {
    const named = item.title || item.question || item.author || item.name || item.label;
    if (named) return named;
    const text = Object.values(item).find((value) => typeof value === 'string' && value.trim() !== '');
    if (text) return text;
  }
  return `${label} ${index + 1}`;
};

/**
 * One control. `compact` is for a field inside a list item, where the
 * placeholder stands in for the label so the rows stay short, as they
 * always have; the label is still given to assistive technology.
 */
function Control({ field, value, onChange, name, compact }) {
  const [picking, setPicking] = useState(false);
  const placeholder = field.placeholder ?? (compact ? field.label : undefined);

  switch (field.type) {
    case 'textarea':
    case 'richtext':
      return (
        <textarea
          className={`${inputCls} h-16 resize-none`}
          value={value ?? ''}
          placeholder={placeholder}
          aria-label={compact ? name : undefined}
          onChange={(e) => onChange(e.target.value)}
        />
      );
    case 'number':
      return (
        <input
          className={inputCls}
          type="number"
          step="any"
          value={value ?? ''}
          placeholder={placeholder}
          aria-label={compact ? name : undefined}
          onChange={(e) => onChange(e.target.value === '' ? null : Number(e.target.value))}
        />
      );
    case 'boolean':
      // Sits inside a <label> on its own, so needs no name of its own.
      return (
        <input
          type="checkbox"
          className="rounded border-[var(--color-rule)]"
          checked={!!value}
          onChange={(e) => onChange(e.target.checked)}
        />
      );
    case 'select':
      return (
        <select
          className={inputCls}
          value={String(value ?? field.options[0]?.value ?? '')}
          aria-label={compact ? name : undefined}
          // An option's value may be a number; save it as one.
          onChange={(e) => onChange(field.options.find((option) => String(option.value) === e.target.value)?.value ?? e.target.value)}
        >
          {field.options.map((option) => (
            <option key={String(option.value)} value={String(option.value)}>{option.label}</option>
          ))}
        </select>
      );
    case 'image':
      return (
        <div className="flex gap-2">
          <input
            className={inputCls}
            value={value ?? ''}
            placeholder={placeholder ?? '/storage/… or https://…'}
            aria-label={compact ? name : undefined}
            onChange={(e) => onChange(e.target.value)}
          />
          <button
            type="button"
            onClick={() => setPicking(true)}
            title="Choose from the media library"
            aria-label={`Choose ${name} from the media library`}
            className="icon-button shrink-0"
          >
            <ImageSquare className="h-4 w-4" />
          </button>
          <MediaPicker
            isOpen={picking}
            currentUrl={value ?? ''}
            onClose={() => setPicking(false)}
            onSelect={(url) => { onChange(url); setPicking(false); }}
          />
        </div>
      );
    default:
      // text and url: a url may be a path on the site, so it is not type="url".
      return (
        <input
          className={inputCls}
          inputMode={field.type === 'url' ? 'url' : undefined}
          value={value ?? ''}
          placeholder={placeholder}
          aria-label={compact ? name : undefined}
          onChange={(e) => onChange(e.target.value)}
        />
      );
  }
}

/** A field with its label, or a whole list. */
function FieldRow({ field, value, onChange, name, compact }) {
  if (field.type === 'list') {
    return <ListField field={field} value={value} onChange={onChange} nested={compact} />;
  }
  if (field.type === 'boolean') {
    return (
      <label className="flex items-center gap-2 text-sm text-[var(--color-ink-2)]">
        <Control field={field} value={value} onChange={onChange} name={name} compact={compact} />
        {field.label}
      </label>
    );
  }
  if (compact && field.type !== 'textarea' && field.type !== 'richtext' && field.type !== 'select') {
    return <Control field={field} value={value} onChange={onChange} name={name} compact />;
  }
  return (
    <Field label={field.label}>
      <Control field={field} value={value} onChange={onChange} name={name} />
    </Field>
  );
}

/** A repeatable list: add, remove and reorder its items, and edit each. */
function ListField({ field, value, onChange, nested }) {
  const items = Array.isArray(value) ? value : [];
  const scalar = typeof field.item === 'string';
  const itemFields = scalar ? [] : Object.entries(field.item).filter(([, child]) => shownInPanel(child));

  const set = (next) => onChange(next);
  const move = (from, to) => {
    if (to < 0 || to >= items.length) return;
    const next = [...items];
    [next[from], next[to]] = [next[to], next[from]];
    set(next);
  };
  const setItem = (index, next) => set(items.map((item, i) => (i === index ? next : item)));

  const Heading = nested ? 'h4' : 'h3';

  return (
    <div>
      <div className="mb-2 flex items-center justify-between">
        <Heading className="text-xs font-semibold uppercase tracking-wide text-[var(--color-muted)]">
          {field.label} items ({items.length})
        </Heading>
        <button
          type="button"
          onClick={() => set([...items, newValue(scalar ? { type: field.item, label: field.label } : field)])}
          aria-label={`Add ${field.label}`}
          className="inline-flex min-h-11 items-center gap-1 text-sm font-medium text-[var(--color-ink)]"
        >
          <Plus className="h-4 w-4" weight="bold" /> Add
        </button>
      </div>
      <ul className="space-y-1.5">
        {items.map((item, index) => {
          const itemName = `${field.label} ${index + 1}`;
          return (
            <li key={index} className="rounded-lg border border-[var(--color-rule-2)] px-2.5 py-2">
              <div className="flex items-center gap-1">
                <span className="w-5 shrink-0 text-xs text-[var(--color-muted)]">{index + 1}</span>
                {scalar ? (
                  <div className="min-w-0 flex-1">
                    <Control field={{ type: field.item, label: field.label }} value={item} onChange={(next) => setItem(index, next)} name={itemName} compact />
                  </div>
                ) : (
                  <span className="min-w-0 flex-1 truncate text-sm text-[var(--color-ink-2)]">{summaryOf(item, field.label, index)}</span>
                )}
                <RowButton title={`Move ${itemName} up`} disabled={index === 0} onClick={() => move(index, index - 1)}>
                  <CaretUp className="h-4 w-4" weight="bold" />
                </RowButton>
                <RowButton title={`Move ${itemName} down`} disabled={index === items.length - 1} onClick={() => move(index, index + 1)}>
                  <CaretDown className="h-4 w-4" weight="bold" />
                </RowButton>
                <RowButton title={`Remove ${itemName}`} onClick={() => set(items.filter((_, i) => i !== index))}>
                  <X className="h-4 w-4" />
                </RowButton>
              </div>
              {itemFields.length > 0 && (
                <div className="mt-2 grid gap-2">
                  {itemFields.map(([key, child]) => (
                    <FieldRow
                      key={key}
                      field={child}
                      value={item?.[key]}
                      name={`${itemName} ${child.label}`}
                      onChange={(next) => setItem(index, { ...(item || {}), [key]: next })}
                      compact
                    />
                  ))}
                </div>
              )}
            </li>
          );
        })}
      </ul>
    </div>
  );
}

function RowButton({ children, onClick, title, disabled }) {
  return (
    <button
      type="button"
      onClick={onClick}
      title={title}
      aria-label={title}
      disabled={disabled}
      className="min-h-11 min-w-9 shrink-0 text-[var(--color-muted)] hover:text-[var(--color-ink)] disabled:opacity-30"
    >
      {children}
    </button>
  );
}

/**
 * Every panel field of a section type, in schema order.
 *
 * @param fields   the type's fields, path => field
 * @param read     (path) => the section's value at that path
 * @param write    (path, value) => store a value at that path
 */
export default function SchemaFields({ fields, read, write }) {
  return Object.entries(fields)
    .filter(([, field]) => shownInPanel(field))
    .map(([path, field]) => (
      <FieldRow key={path} field={field} value={read(path)} onChange={(value) => write(path, value)} name={field.label} />
    ));
}

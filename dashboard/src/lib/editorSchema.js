// Helpers for the visual editor's sections.
//
// Which section types a page may hold, what each starts with and which
// controls the side panel draws for it all come from the server
// (GET /section-types): the built-in types from resources/sections/base.json,
// plus the ones the page's theme declares in its theme.json. Text inside
// sections is edited inline on the preview.

let uidCounter = 0;
export const makeUid = () => `s_${Date.now().toString(36)}_${uidCounter++}`;

/**
 * A new section of a type, from its schema's defaults. A type the page's
 * theme does not know starts empty; the server will refuse it on save.
 *
 * @param {string} type
 * @param {Array<{type: string, defaults: object}>|null} sectionTypes
 */
export const createSection = (type, sectionTypes) => {
  const schema = (sectionTypes || []).find((entry) => entry.type === type);
  const defaults = schema ? structuredClone(schema.defaults) : { title: '', content: '', data: {} };
  return {
    key: type,
    type,
    title: defaults.title ?? '',
    content: defaults.content ?? '',
    // PHP sends an empty object as [].
    data: Array.isArray(defaults.data) ? {} : (defaults.data || {}),
    _uid: makeUid(),
    is_visible: true,
  };
};

// A copy of one level of the path, ready to take `key`. A list stays a list
// only for a numeric key: the server sends empty data as [], and a named key
// set on a list would be lost when the page is sent back.
const copyFor = (value, key) => {
  if (Array.isArray(value)) return /^\d+$/.test(key) ? [...value] : { ...value };
  return { ...(value || {}) };
};

// Read/write a dotted path (e.g. "primary_cta.label") on a plain object,
// returning a shallow-cloned object so React state updates stay immutable.
export const setPath = (obj, path, value) => {
  const keys = path.split('.');
  const next = copyFor(obj, keys[0]);
  let cursor = next;
  for (let i = 0; i < keys.length - 1; i++) {
    const k = keys[i];
    cursor[k] = copyFor(cursor[k], keys[i + 1]);
    cursor = cursor[k];
  }
  cursor[keys[keys.length - 1]] = value;
  return next;
};

// ── Section schemas ───────────────────────────────────────
// The server describes every section type a page may hold, built-in or
// declared by its theme, with the fields the side panel draws
// (GET /section-types). These read that description.

// Whether the side panel shows a field. Text edited on the preview is
// marked `inspector: false`.
export const shownInPanel = (field) => field.inspector !== false;

// The value at a dotted path (e.g. "data.primary_cta.url") of a section.
export const getPath = (obj, path) => path.split('.')
  .reduce((value, key) => (value == null ? undefined : value[key]), obj);

const EMPTY = { boolean: false, number: null, list: [] };

/**
 * What a new list item, or a new value of a field, starts as: its `default`
 * when the schema gives one, else empty. A required text field starts as its
 * own label, so a fresh item can be saved and reads as something to replace.
 */
export const newValue = (field) => {
  if (field.type === 'list') {
    return typeof field.item === 'string'
      ? newValue({ type: field.item, label: field.label })
      : Object.fromEntries(Object.entries(field.item).map(([key, child]) => [
        key, 'default' in child ? structuredClone(child.default) : emptyFor(child),
      ]));
  }
  return 'default' in field ? structuredClone(field.default) : emptyFor(field);
};

const emptyFor = (field) => {
  if (field.type in EMPTY) return structuredClone(EMPTY[field.type]);
  if (field.type === 'select') return field.options[0]?.value ?? '';
  if (field.required && ['text', 'textarea', 'richtext'].includes(field.type)) return field.label;
  return '';
};

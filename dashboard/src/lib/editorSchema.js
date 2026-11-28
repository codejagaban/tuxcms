// Registry of section types the visual editor understands.
//
// Each entry describes how to create a new section of that type and, where the
// section contains a repeatable list (feature cards, FAQ entries, etc.), how to
// add a fresh item. Text inside sections is edited inline on the preview; this
// schema drives the structural controls in the Inspector.

import SECTION_DEFAULTS from './sectionDefaults.json';

// What a new section of each type starts with: its title, content and data.
// It lives in JSON so the server's tests read the very same defaults when
// they check that every theme marks every field (ThemeMarkerContractTest).
// A contact form's blank access_key falls back to the site-wide
// web3forms_access_key setting.
const fromDefaults = (type) => ({ key: type, type, ...structuredClone(SECTION_DEFAULTS[type]) });

let uidCounter = 0;
export const makeUid = () => `s_${Date.now().toString(36)}_${uidCounter++}`;

// type -> { label, blurb, create(), list? }
export const SECTION_REGISTRY = {
  hero: {
    label: 'Hero',
    blurb: 'Large banner with heading and call-to-action.',
    create: () => fromDefaults('hero'),
  },

  text: {
    label: 'Text',
    blurb: 'A rich text content block.',
    create: () => fromDefaults('text'),
  },

  features: {
    label: 'Features',
    blurb: 'A grid of feature cards (icon, title, description).',
    create: () => fromDefaults('features'),
    list: {
      path: 'items',
      label: 'Feature',
      item: () => ({ icon: 'star', title: 'New feature', description: 'Describe the benefit here.', image: '', url: '', link_label: '' }),
    },
  },

  stats: {
    label: 'Stats',
    blurb: 'A row of numbers / counters.',
    create: () => fromDefaults('stats'),
    list: {
      path: 'items',
      label: 'Stat',
      item: () => ({ value: '0', label: 'New stat' }),
    },
  },

  testimonials: {
    label: 'Testimonials',
    blurb: 'Quotes from customers.',
    create: () => fromDefaults('testimonials'),
    list: {
      path: 'items',
      label: 'Quote',
      item: () => ({ quote: 'A short, specific quote.', author: 'Full Name', role: 'Title, Company' }),
    },
  },

  team: {
    label: 'Team',
    blurb: 'People cards with name, role and bio.',
    create: () => fromDefaults('team'),
    list: {
      path: 'members',
      label: 'Member',
      item: () => ({ name: 'Full Name', role: 'Role', bio: 'One line about this person.' }),
    },
  },

  faq: {
    label: 'FAQ',
    blurb: 'Question and answer pairs.',
    create: () => fromDefaults('faq'),
    list: {
      path: 'items',
      label: 'Question',
      item: () => ({ question: 'A common question?', answer: 'A clear, helpful answer.' }),
    },
  },

  cta: {
    label: 'Call to action',
    blurb: 'A focused prompt with a button.',
    create: () => fromDefaults('cta'),
  },

  contact: {
    label: 'Contact details',
    blurb: 'Email, phone, address and hours.',
    create: () => fromDefaults('contact'),
  },

  contact_form: {
    label: 'Contact form',
    blurb: 'A contact form powered by Web3Forms.',
    create: () => fromDefaults('contact_form'),
    list: {
      path: 'fields',
      label: 'Field',
      item: () => ({ name: 'field', label: 'New field', type: 'text', required: false }),
    },
  },

  map: {
    label: 'Map',
    blurb: 'An embedded location map.',
    create: () => fromDefaults('map'),
  },
};

// The order that the "Add section" menu presents types in.
export const SECTION_MENU = [
  'hero', 'text', 'features', 'stats', 'testimonials',
  'team', 'faq', 'cta', 'contact', 'contact_form', 'map',
];

export const sectionLabel = (type) => SECTION_REGISTRY[type]?.label || type;

export const createSection = (type) => {
  const entry = SECTION_REGISTRY[type];
  const base = entry ? entry.create() : { key: type, type, title: '', content: '', data: {} };
  return { ...base, _uid: makeUid(), is_visible: true };
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

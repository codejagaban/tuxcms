// Registry of section types the visual editor understands.
//
// Each entry describes how to create a new section of that type and, where the
// section contains a repeatable list (feature cards, FAQ entries, etc.), how to
// add a fresh item. Text inside sections is edited inline on the preview; this
// schema drives the structural controls in the Inspector.

let uidCounter = 0;
export const makeUid = () => `s_${Date.now().toString(36)}_${uidCounter++}`;

// type -> { label, blurb, create(), list? }
export const SECTION_REGISTRY = {
  hero: {
    label: 'Hero',
    blurb: 'Large banner with heading and call-to-action.',
    create: () => ({
      key: 'hero',
      type: 'hero',
      title: 'Your headline goes here',
      content: 'A short supporting sentence that explains the value.',
      data: {
        subheading: 'Eyebrow text',
        alignment: 'center',
        background_image: '',
        primary_cta: { label: 'Get started', url: '/contact' },
        secondary_cta: { label: 'Learn more', url: '/about' },
      },
    }),
  },

  text: {
    label: 'Text',
    blurb: 'A rich text content block.',
    create: () => ({
      key: 'text',
      type: 'text',
      title: 'Section heading',
      content: 'Write your content here. Click to edit this text directly.',
      data: {},
    }),
  },

  features: {
    label: 'Features',
    blurb: 'A grid of feature cards (icon, title, description).',
    create: () => ({
      key: 'features',
      type: 'features',
      title: 'What we offer',
      content: '',
      data: {
        columns: 3,
        items: [
          { icon: 'sparkles', title: 'Feature one', description: 'Describe the benefit here.' },
          { icon: 'zap', title: 'Feature two', description: 'Describe the benefit here.' },
          { icon: 'shield', title: 'Feature three', description: 'Describe the benefit here.' },
        ],
      },
    }),
    list: {
      path: 'items',
      label: 'Feature',
      item: () => ({ icon: 'star', title: 'New feature', description: 'Describe the benefit here.' }),
    },
  },

  stats: {
    label: 'Stats',
    blurb: 'A row of numbers / counters.',
    create: () => ({
      key: 'stats',
      type: 'stats',
      title: 'By the numbers',
      content: '',
      data: {
        items: [
          { value: '100+', label: 'Projects' },
          { value: '4.9/5', label: 'Rating' },
          { value: '10', label: 'Years' },
        ],
      },
    }),
    list: {
      path: 'items',
      label: 'Stat',
      item: () => ({ value: '0', label: 'New stat' }),
    },
  },

  testimonials: {
    label: 'Testimonials',
    blurb: 'Quotes from customers.',
    create: () => ({
      key: 'testimonials',
      type: 'testimonials',
      title: 'What people say',
      content: '',
      data: {
        items: [
          { quote: 'A glowing quote about the work.', author: 'Full Name', role: 'Title, Company' },
        ],
      },
    }),
    list: {
      path: 'items',
      label: 'Quote',
      item: () => ({ quote: 'A short, specific quote.', author: 'Full Name', role: 'Title, Company' }),
    },
  },

  team: {
    label: 'Team',
    blurb: 'People cards with name, role and bio.',
    create: () => ({
      key: 'team',
      type: 'team',
      title: 'The team',
      content: '',
      data: {
        members: [
          { name: 'Full Name', role: 'Role', bio: 'One line about this person.' },
        ],
      },
    }),
    list: {
      path: 'members',
      label: 'Member',
      item: () => ({ name: 'Full Name', role: 'Role', bio: 'One line about this person.' }),
    },
  },

  faq: {
    label: 'FAQ',
    blurb: 'Question and answer pairs.',
    create: () => ({
      key: 'faq',
      type: 'faq',
      title: 'Frequently asked questions',
      content: '',
      data: {
        items: [
          { question: 'A common question?', answer: 'A clear, helpful answer.' },
        ],
      },
    }),
    list: {
      path: 'items',
      label: 'Question',
      item: () => ({ question: 'A common question?', answer: 'A clear, helpful answer.' }),
    },
  },

  cta: {
    label: 'Call to action',
    blurb: 'A focused prompt with a button.',
    create: () => ({
      key: 'cta',
      type: 'cta',
      title: 'Ready to get started?',
      content: 'A single sentence nudging the visitor to act.',
      data: { primary_cta: { label: 'Get in touch', url: '/contact' } },
    }),
  },

  contact: {
    label: 'Contact details',
    blurb: 'Email, phone, address and hours.',
    create: () => ({
      key: 'contact',
      type: 'contact',
      title: 'Contact details',
      content: '',
      data: {
        email: 'hello@example.com',
        phone: '+1 555 000 0000',
        address: '123 Example St, City',
        hours: 'Mon–Fri, 9:00–17:00',
      },
    }),
  },

  form: {
    label: 'Embedded form',
    blurb: 'Show one of your forms on the page.',
    create: () => ({
      key: 'form',
      type: 'form',
      title: 'Send a message',
      content: '',
      data: { form_id: null, form_slug: null },
    }),
  },
};

// The order that the "Add section" menu presents types in.
export const SECTION_MENU = [
  'hero', 'text', 'features', 'stats', 'testimonials',
  'team', 'faq', 'cta', 'contact', 'form',
];

export const sectionLabel = (type) => SECTION_REGISTRY[type]?.label || type;

export const createSection = (type) => {
  const entry = SECTION_REGISTRY[type];
  const base = entry ? entry.create() : { key: type, type, title: '', content: '', data: {} };
  return { ...base, _uid: makeUid(), is_visible: true };
};

// Read/write a dotted path (e.g. "primary_cta.label") on a plain object,
// returning a shallow-cloned object so React state updates stay immutable.
export const setPath = (obj, path, value) => {
  const keys = path.split('.');
  const next = Array.isArray(obj) ? [...obj] : { ...(obj || {}) };
  let cursor = next;
  for (let i = 0; i < keys.length - 1; i++) {
    const k = keys[i];
    cursor[k] = Array.isArray(cursor[k]) ? [...cursor[k]] : { ...(cursor[k] || {}) };
    cursor = cursor[k];
  }
  cursor[keys[keys.length - 1]] = value;
  return next;
};

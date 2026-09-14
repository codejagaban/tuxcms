import { useMemo, useRef } from 'react';

const normalise = (value = '') => value.replace(/\s+/g, ' ').trim();

function editableFields(sections) {
  return sections.flatMap((section) => {
    const fields = [
      ['title', section.title],
      ['content', section.content],
      ['data.subheading', section.data?.subheading],
      ['data.eyebrow', section.data?.eyebrow],
      ['data.primary_cta.label', section.data?.primary_cta?.label],
      ['data.secondary_cta.label', section.data?.secondary_cta?.label],
    ];

    (section.data?.items || []).forEach((item, index) => {
      ['title', 'description', 'quote', 'author', 'role'].forEach((field) => {
        fields.push([`data.items.${index}.${field}`, item?.[field]]);
      });
    });

    return fields
      .filter(([, value]) => typeof value === 'string' && normalise(value).length > 1)
      .map(([field, value]) => ({ sectionUid: section._uid, field, value: normalise(value) }));
  });
}

/** Render the generated Crystal page itself so the editor cannot drift. */
export default function CrystalCanvas({ path, revision = 0, mobile = false, sections = [], onEdit, onSelect }) {
  const frameRef = useRef(null);
  const fields = useMemo(() => editableFields(sections), [sections]);
  const separator = path.includes('?') ? '&' : '?';
  const src = `${path}${separator}tuxcms_preview=${revision}`;

  const preparePreview = () => {
    try {
      const doc = frameRef.current?.contentDocument;
      if (doc?.body) {
        const style = doc.querySelector('style[data-tuxcms-preview]') || doc.createElement('style');
        style.dataset.tuxcmsPreview = 'true';
        style.textContent = `
          .wow { visibility: visible !important; animation: none !important; }
          [data-tuxcms-editable] { cursor: text; outline: 1px solid transparent; outline-offset: 5px; }
          [data-tuxcms-editable]:hover { outline-color: rgba(17, 17, 17, .38); }
          [data-tuxcms-editable]:focus { outline: 2px solid #111; }
        `;
        if (!style.isConnected) doc.head.appendChild(style);

        const installEditors = () => {
          const candidates = [...doc.querySelectorAll('h1, h2, h3, h4, h5, h6, p, a, span, blockquote, footer, div')];
          const claimed = new Set();

          fields.forEach((field) => {
            const match = candidates
              .filter((element) => !claimed.has(element) && normalise(element.textContent) === field.value)
              .sort((a, b) => a.children.length - b.children.length)[0];

            if (!match) return;
            claimed.add(match);
            if (match.dataset.tuxcmsEditable === 'true') return;
            match.dataset.tuxcmsEditable = 'true';
            match.contentEditable = 'true';
            match.spellcheck = true;
            match.addEventListener('pointerdown', () => {
              // Crystal's heading animation wraps characters in spans. Remove
              // those wrappers before the browser places the caret so editing
              // behaves like a normal text field without duplicating content.
              if (match.children.length) match.textContent = field.value;
            });
            match.addEventListener('click', (event) => {
              event.stopPropagation();
              onSelect?.(field.sectionUid);
            });
            match.addEventListener('keydown', (event) => {
              if (event.key === 'Enter') {
                event.preventDefault();
                match.blur();
              }
            });
            match.addEventListener('blur', () => {
              const value = normalise(match.textContent);
              if (value && value !== field.value) onEdit?.(field.sectionUid, field.field, value);
            });
          });
        };

        installEditors();
        const view = frameRef.current?.contentWindow;
        view?.requestAnimationFrame(() => view.requestAnimationFrame(installEditors));
      }
    } catch {
      // A custom cross-origin preview still works, without the editor override.
    }
  };

  return (
    <iframe
      ref={frameRef}
      key={src}
      title="Exact Crystal website preview"
      src={src}
      onLoad={preparePreview}
      className="block w-full border-0 bg-white"
      style={{ height: '100%', minWidth: mobile ? 390 : 960 }}
    />
  );
}

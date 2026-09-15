import { useMemo, useRef } from 'react';

const normalise = (value = '') => value.replace(/\s+/g, ' ').trim();

function editableFields(sections) {
  const ignoredKeys = new Set(['url', 'href', 'image', 'image_alt', 'background_image', 'src']);

  const visit = (value, path, fields) => {
    if (typeof value === 'string') {
      const key = path.split('.').at(-1);
      if (!ignoredKeys.has(key) && normalise(value).length > 1) fields.push([path, value]);
      return;
    }
    if (!value || typeof value !== 'object') return;
    Object.entries(value).forEach(([key, child]) => visit(child, path ? `${path}.${key}` : key, fields));
  };

  return sections.flatMap((section) => {
    const fields = [];
    visit(section.title, 'title', fields);
    visit(section.content, 'content', fields);
    visit(section.data, 'data', fields);
    return fields.map(([field, value]) => ({ sectionUid: section._uid, field, value: normalise(value) }));
  });
}

function editableImages(sections) {
  const imageKeys = new Set(['image', 'image_url', 'background_image', 'src']);

  const visit = (value, path, sectionUid, images, parent = null) => {
    if (!value || typeof value !== 'object') return;
    Object.entries(value).forEach(([key, child]) => {
      const field = path ? `${path}.${key}` : key;
      if (typeof child === 'string' && imageKeys.has(key) && child.trim()) {
        const altKey = key === 'image' ? 'image_alt' : `${key}_alt`;
        images.push({
          sectionUid,
          field: `data.${field}`,
          altField: parent && Object.hasOwn(parent, altKey) ? `data.${path ? `${path}.` : ''}${altKey}` : null,
          url: child,
          alt: parent?.[altKey] || parent?.title || '',
        });
      } else if (child && typeof child === 'object') {
        visit(child, field, sectionUid, images, child);
      }
    });
  };

  return sections.flatMap((section) => {
    const images = [];
    visit(section.data, '', section._uid, images, section.data);
    return images;
  });
}

const imagePath = (url = '', base = 'http://localhost') => {
  try { return new URL(url, base).pathname; } catch { return url; }
};

/** Render the generated Crystal page itself so the editor cannot drift. */
export default function CrystalCanvas({ path, revision = 0, mobile = false, sections = [], pages = [], overrides = {}, onEdit, onOverrideEdit, onSelect, onImageEdit, onPageNavigate }) {
  const frameRef = useRef(null);
  const fields = useMemo(() => editableFields(sections), [sections]);
  const images = useMemo(() => editableImages(sections), [sections]);
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
          [data-tuxcms-image-editable] { cursor: pointer; outline: 1px solid transparent; outline-offset: 5px; }
          [data-tuxcms-image-editable]:hover { outline: 2px solid #111; }
        `;
        if (!style.isConnected) doc.head.appendChild(style);

        const installEditors = () => {
          const candidates = [...doc.querySelectorAll('h1, h2, h3, h4, h5, h6, p, a, span, blockquote, footer, div')];
          const claimed = new Set();

          const makeTextEditable = (match, field, initialValue) => {
            if (match.dataset.tuxcmsEditable === 'true') return;
            match.dataset.tuxcmsEditable = 'true';
            match.contentEditable = 'true';
            match.spellcheck = true;
            match.addEventListener('pointerdown', () => {
              // Crystal's heading animation wraps characters in spans. Remove
              // those wrappers before the browser places the caret so editing
              // behaves like a normal text field without duplicating content.
              if (match.children.length) match.textContent = initialValue;
            });
            match.addEventListener('click', (event) => {
              event.preventDefault();
              event.stopPropagation();
              event.stopImmediatePropagation();
              if (field.sectionUid) onSelect?.(field.sectionUid);
            });
            match.addEventListener('keydown', (event) => {
              if (event.key === 'Enter') {
                event.preventDefault();
                match.blur();
              }
            });
            match.addEventListener('blur', () => {
              const value = normalise(match.textContent);
              if (!value || value === initialValue) return;
              if (field.overrideKey != null) onOverrideEdit?.('text', field.overrideKey, value);
              else onEdit?.(field.sectionUid, field.field, value);
            });
          };

          fields.forEach((field) => {
            const match = candidates
              .filter((element) => !claimed.has(element) && normalise(element.textContent) === field.value)
              .sort((a, b) => a.children.length - b.children.length)[0];

            if (!match) return;
            claimed.add(match);
            makeTextEditable(match, field, field.value);
          });

          const templateText = [...doc.querySelectorAll('main h1, main h2, main h3, main h4, main h5, main h6, main p, main blockquote, main .features-list-text, main .alt-features-descr, main a span')];
          templateText.forEach((element, index) => {
            if (claimed.has(element) || normalise(element.textContent).length <= 1) return;
            const value = normalise(overrides.text?.[index] || element.textContent);
            if (overrides.text?.[index]) element.textContent = value;
            makeTextEditable(element, { overrideKey: index }, value);
          });
        };

        const claimedImages = new Set();
        images.forEach((image) => {
          const match = [...doc.images].find((element) => {
            if (claimedImages.has(element)) return false;
            return imagePath(element.src, doc.baseURI) === imagePath(image.url, doc.baseURI);
          });
          if (!match) return;
          claimedImages.add(match);
          match.dataset.tuxcmsImageEditable = 'true';
          match.title = 'Click to replace image';
          match.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            onSelect?.(image.sectionUid);
            onImageEdit?.(image, match);
          });
        });

        const templateImages = [...doc.querySelectorAll('main img')].filter((element) => {
          const alt = normalise(element.alt || '');
          const source = imagePath(element.src, doc.baseURI);
          return alt && !/(decoration|bg-shape|logo|favicon)/i.test(source);
        });
        templateImages.forEach((element, index) => {
          if (claimedImages.has(element)) return;
          const replacement = overrides.images?.[index];
          if (replacement?.url) element.src = replacement.url;
          if (replacement?.alt) element.alt = replacement.alt;
          element.dataset.tuxcmsImageEditable = 'true';
          element.title = 'Click to replace image';
          element.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            onImageEdit?.({ overrideKey: index, url: element.src, alt: element.alt }, element);
          });
        });

        const editableRoutes = new Map(pages.map((page) => {
          const route = page.is_homepage ? '/' : `/${String(page.path || page.slug || '').replace(/^\/+|\/+$/g, '')}/`;
          return [route, page];
        }));
        doc.querySelectorAll('a[href]').forEach((link) => {
          const url = new URL(link.href, doc.baseURI);
          if (url.origin !== doc.location.origin) return;
          const route = url.pathname === '/' ? '/' : `${url.pathname.replace(/\/+$/g, '')}/`;
          const destination = editableRoutes.get(route);
          if (!destination) return;
          link.title = `Edit ${destination.title}`;
          link.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            onPageNavigate?.(destination.id);
          });
        });

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
      key={`${src}:${pages.length}:${sections.length}`}
      title="Exact Crystal website preview"
      src={src}
      onLoad={preparePreview}
      className="block w-full border-0 bg-white"
      style={{ height: '100%', minWidth: mobile ? 390 : 960 }}
    />
  );
}

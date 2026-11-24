import { useEffect, useMemo, useRef } from 'react';

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
    return fields.flatMap(([field, value]) => {
      const base = { sectionUid: section._uid, sectionKey: String(section.id ?? section._uid), field };
      // Text written as several paragraphs (a blank line between each) is
      // shown as several elements, so each paragraph is its own editable part.
      const paragraphs = value.split(/\n\s*\n/).map(normalise).filter(Boolean);
      if (paragraphs.length < 2) return [{ ...base, value: normalise(value) }];
      return paragraphs.map((paragraph, part) => ({ ...base, value: paragraph, part, paragraphs }));
    });
  });
}

function editableImages(sections) {
  // A section can hold several photos: image, image_2, image_3 and so on.
  const isImageKey = (key) => /^(image|image_url|background_image|src)(_\d+)?$/.test(key);

  const visit = (value, path, sectionUid, images, parent = null) => {
    if (!value || typeof value !== 'object') return;
    Object.entries(value).forEach(([key, child]) => {
      const field = path ? `${path}.${key}` : key;
      if (typeof child === 'string' && isImageKey(key) && child.trim()) {
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

const fieldKey = (field) => `section:${field.sectionKey}:${field.field}${field.part != null ? `#${field.part}` : ''}`;

const imagePath = (url = '', base = 'http://localhost') => {
  try { return new URL(url, base).pathname; } catch { return url; }
};

/** Render the generated Crystal page itself so the editor cannot drift. */
export default function CrystalCanvas({ path, revision = 0, mobile = false, sections = [], pages = [], overrides = {}, allowOverrides = true, onEdit, onOverrideEdit, onSelect, onImageEdit, onPageNavigate }) {
  const frameRef = useRef(null);
  const fields = useMemo(() => editableFields(sections), [sections]);
  const images = useMemo(() => editableImages(sections), [sections]);
  const separator = path.includes('?') ? '&' : '?';
  const src = `${path}${separator}tuxcms_preview=${revision}`;

  // Keep the exact preview in sync when data changes outside the iframe, such
  // as restoring a revision or editing through the inspector.
  useEffect(() => {
    const doc = frameRef.current?.contentDocument;
    if (!doc) return;
    const nodes = [...doc.querySelectorAll('[data-tuxcms-field-key]')];
    fields.forEach((field) => {
      const key = fieldKey(field);
      const node = nodes.find((element) => element.dataset.tuxcmsFieldKey === key);
      if (node && normalise(node.textContent) !== field.value) node.textContent = field.value;
    });
    Object.entries(overrides.text || {}).forEach(([index, value]) => {
      const node = nodes.find((element) => element.dataset.tuxcmsFieldKey === `override:${index}`);
      if (node && normalise(node.textContent) !== normalise(value)) node.textContent = value;
    });
  }, [fields, overrides]);

  const preparePreview = () => {
    try {
      const doc = frameRef.current?.contentDocument;
      if (doc?.body) {
        const style = doc.querySelector('style[data-tuxcms-preview]') || doc.createElement('style');
        style.dataset.tuxcmsPreview = 'true';
        style.textContent = `
          .wow { visibility: visible !important; animation: none !important; }
          .has-motion [data-reveal] { opacity: 1 !important; transform: none !important; }
          [data-tuxcms-editable] { cursor: text; outline: 1px solid transparent; outline-offset: 5px; }
          [data-tuxcms-editable]:hover { outline-color: rgba(17, 17, 17, .38); }
          [data-tuxcms-editable]:focus { outline: 2px solid #111; }
          [data-tuxcms-image-editable] { cursor: pointer; outline: 1px solid transparent; outline-offset: 5px; }
          [data-tuxcms-image-editable]:hover { outline: 2px solid #111; }
          [data-tuxcms-on-dark][data-tuxcms-editable]:hover { outline-color: rgba(255, 255, 255, .55); }
          [data-tuxcms-on-dark][data-tuxcms-editable]:focus { outline-color: #fff; }
          [data-tuxcms-on-dark][data-tuxcms-image-editable]:hover { outline-color: #fff; }
          .tuxcms-photo-button { position: absolute; z-index: 2147483000; margin: 0; padding: 7px 12px; border: 0; border-radius: 6px; background: #111; color: #fff; font: 600 12px/1 system-ui, sans-serif; letter-spacing: 0; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, .35); }
          .tuxcms-photo-button:hover, .tuxcms-photo-button:focus-visible { background: #fff; color: #111; outline: 2px solid #111; }
        `;
        if (!style.isConnected) doc.head.appendChild(style);

        const installEditors = () => {
          // Crystal's character animation adds a visually-hidden text clone
          // beside the animated span. A parent heading therefore contains the
          // same words twice, which can be mistaken for the editable value.
          // The CMS preview does not animate text, so collapse that structure
          // to one accessible, plain-text node before matching fields.
          doc.querySelectorAll('[data-splitting="chars"]').forEach((element) => {
            const accessibilityClone = element.previousElementSibling;
            if (accessibilityClone?.classList.contains('visually-hidden')) {
              accessibilityClone.remove();
            }
            element.removeAttribute('aria-hidden');
            element.removeAttribute('data-splitting');
            element.textContent = normalise(element.textContent);
          });

          const candidates = [...doc.querySelectorAll('h1, h2, h3, h4, h5, h6, p, a, span, blockquote, footer, div')]
            .filter((element) => !element.closest('form'));
          const claimed = new Set();

          const makeTextEditable = (match, field, initialValue) => {
            if (match.dataset.tuxcmsEditable === 'true') return;
            match.dataset.tuxcmsEditable = 'true';
            match.dataset.tuxcmsFieldKey = field.overrideKey != null
              ? `override:${field.overrideKey}`
              : fieldKey(field);
            match.contentEditable = 'true';
            match.setAttribute('role', 'textbox');
            match.setAttribute('aria-label', `Edit ${match.tagName.toLowerCase()} text`);
            match.setAttribute('aria-multiline', 'false');
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
              else if (field.part != null) {
                // Save the whole text again: every paragraph as it now reads
                // on the page, with the blank lines put back between them.
                const whole = field.paragraphs.map((paragraph, part) => {
                  const node = doc.querySelector(`[data-tuxcms-field-key="${fieldKey({ ...field, part })}"]`);
                  return (node && normalise(node.textContent)) || paragraph;
                });
                onEdit?.(field.sectionUid, field.field, whole.join('\n\n'));
              }
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

          const templateText = [...doc.querySelectorAll('main h1, main h2, main h3, main h4, main h5, main h6, main p, main blockquote, main .features-list-text, main .alt-features-descr, main a span, main .contact-item .ci-text')]
            .filter((element) => !element.closest('form') && !element.closest('.contact-item a'));
          templateText.forEach((element, index) => {
            // Only Crystal applies text overrides when it builds the page.
            if (!allowOverrides) return;
            const overlapsClaimedField = [...claimed].some((claimedElement) =>
              element.contains(claimedElement) || claimedElement.contains(element));
            // Never install nested contentEditable nodes. Crystal's animated
            // headings often leave a text span inside the heading wrapper;
            // making both editable causes duplicated text and writes the edit
            // to a template override instead of the matching CMS field.
            if (claimed.has(element) || overlapsClaimedField || normalise(element.textContent).length <= 1) return;
            const value = normalise(overrides.text?.[index] || element.textContent);
            if (overrides.text?.[index]) element.textContent = value;
            makeTextEditable(element, { overrideKey: index }, value);
          });
        };

        // A photo that sits under a heading, a link or a tint never receives
        // the click itself, so every changeable photo also gets a button laid
        // over its corner, above everything else on the page.
        doc.querySelectorAll('.tuxcms-photo-button').forEach((button) => button.remove());
        const photoButtons = [];
        const addPhotoButton = (element, handler) => {
          const button = doc.createElement('button');
          button.type = 'button';
          button.className = 'tuxcms-photo-button';
          button.textContent = 'Change photo';
          button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            handler();
          });
          doc.body.appendChild(button);
          photoButtons.push({ button, element });
        };
        const placePhotoButtons = () => {
          const view = doc.defaultView;
          // A hero photo starts underneath the site's navigation bar, so a
          // button in its corner would be hidden there. Start below the bar.
          // Its height is used, not its position: the bar hides as the page
          // scrolls, which would otherwise move the button with it.
          const bar = doc.querySelector('.site-header, body > header');
          const barBottom = bar ? bar.offsetHeight : 0;
          photoButtons.forEach(({ button, element }) => {
            const box = element.getBoundingClientRect();
            const shown = box.width > 60 && box.height > 40;
            button.hidden = !shown;
            if (!shown) return;
            button.style.left = `${Math.max(box.left, 0) + view.scrollX + 12}px`;
            button.style.top = `${Math.max(box.top + view.scrollY, barBottom) + 12}px`;
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
          addPhotoButton(match, () => {
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
          if (!allowOverrides) return;
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
          addPhotoButton(element, () => onImageEdit?.({ overrideKey: index, url: element.src, alt: element.alt }, element));
        });

        // Keep each button on its photo as the page loads images and resizes.
        placePhotoButtons();
        doc.defaultView.addEventListener('resize', placePhotoButtons);
        doc.defaultView.addEventListener('load', placePhotoButtons);
        if (doc.defaultView.ResizeObserver) new doc.defaultView.ResizeObserver(placePhotoButtons).observe(doc.body);

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

        // The outline is dark by default. Where it would sit on a dark
        // surface it is switched to white, judged when the pointer or the
        // caret arrives so late-loading styles are taken into account.
        if (!doc.documentElement.dataset.tuxcmsContrast) {
          doc.documentElement.dataset.tuxcmsContrast = 'true';
          const probe = doc.createElement('canvas');
          probe.width = probe.height = 1;
          const ink = probe.getContext('2d', { willReadFrequently: true });
          // Any CSS colour, whatever its notation, as [lightness 0-1, alpha 0-1].
          const read = (colour) => {
            ink.clearRect(0, 0, 1, 1);
            ink.fillStyle = '#000';
            ink.fillStyle = colour;
            ink.fillRect(0, 0, 1, 1);
            const [r, g, b, alpha] = ink.getImageData(0, 0, 1, 1).data;
            if (!alpha) return [0, 0];
            const scale = 255 / alpha;
            return [(0.2126 * r + 0.7152 * g + 0.0722 * b) * scale / 255, alpha / 255];
          };
          const onDark = (element) => {
            const styleOf = (node) => doc.defaultView.getComputedStyle(node);
            // The outline is drawn outside the element, so it sits on
            // whatever surrounds it, not on the element's own fill.
            const start = element.parentElement;
            for (let node = start; node && node.nodeType === 1; node = node.parentElement) {
              const style = styleOf(node);
              const [lightness, alpha] = read(style.backgroundColor);
              if (alpha > 0.5) return lightness < 0.45;
              // A photo or gradient cannot be read; light text on it means it is dark.
              if (style.backgroundImage !== 'none') break;
            }
            // A filled element (a button) sets its text against its own
            // fill, so its parent's text colour is the one to read.
            const filled = read(styleOf(element).backgroundColor)[1] > 0.5 || element.matches('img');
            const [lightness, alpha] = read(styleOf(filled && start ? start : element).color);
            return alpha > 0.5 && lightness > 0.6;
          };
          const judge = (event) => {
            const element = event.target?.closest?.('[data-tuxcms-editable], [data-tuxcms-image-editable]');
            if (element) element.toggleAttribute('data-tuxcms-on-dark', onDark(element));
          };
          doc.addEventListener('pointerover', judge);
          doc.addEventListener('focusin', judge);
        }

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
      title="Website preview"
      src={src}
      onLoad={preparePreview}
      className="block w-full border-0 bg-white"
      style={{ height: '100%', minWidth: mobile ? 390 : 960 }}
    />
  );
}

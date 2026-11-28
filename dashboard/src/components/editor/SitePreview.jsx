import { useEffect, useMemo, useRef } from 'react';

const normalise = (value = '') => value.replace(/\s+/g, ' ').trim();

// ── Field markers ─────────────────────────────────────────
// A marked template says exactly which element shows which field:
// data-tuxcms-field="12:data.items.0.question" is section 12's question in its
// first item. Nothing is guessed from the text, so two sections with the same
// words, or text a template reshapes, cannot be confused. An element with no
// marker is not edited.

// "12:data.items.0.question" to ['12', 'data.items.0.question']. Only the
// first colon divides them; the path may contain more.
const parseMarker = (marker = '') => {
  const at = marker.indexOf(':');
  return at > 0 ? [marker.slice(0, at), marker.slice(at + 1)] : null;
};

// The value at a dotted path inside a section.
const valueAt = (section, path) => path.split('.')
  .reduce((value, key) => (value == null ? undefined : value[key]), section);

// Text that may run to several lines and paragraphs: tidy the spaces but keep
// the line breaks, and never more than one blank line in a row.
const tidyLines = (value = '') => value
  .replace(/\r\n?/g, '\n')
  .replace(/[ \t\f\v\u00a0]+/g, ' ')
  .replace(/ *\n */g, '\n')
  .replace(/\n{3,}/g, '\n\n')
  .trim();

// A marked element is edited as several lines when it holds a field that
// already has them, or when the theme wraps the field's paragraphs in it.
const isMultiline = (element, value) => (typeof value === 'string' && value.includes('\n'))
  || element.matches('div, blockquote, section, article')
  || [...element.children].some((child) => child.matches('p, div, ul, ol, blockquote'));

/** Show the real generated page and make it editable, so the editor cannot drift from the site. */
export default function SitePreview({ revision = 0, html, mobile = false, sections = [], pages = [], onEdit, onSelect, onImageEdit, onPageNavigate }) {
  const frameRef = useRef(null);
  // Markers name a section by its saved id.
  const sectionsById = useMemo(() => new Map(sections
    .filter((section) => section.id != null)
    .map((section) => [String(section.id), section])), [sections]);

  // Keep the exact preview in sync when data changes outside the iframe, such
  // as restoring a revision or editing through the inspector.
  useEffect(() => {
    const doc = frameRef.current?.contentDocument;
    if (!doc) return;
    doc.querySelectorAll('[data-tuxcms-field][data-tuxcms-editable]').forEach((node) => {
      const marker = parseMarker(node.dataset.tuxcmsField);
      const section = marker && sectionsById.get(marker[0]);
      const value = section ? valueAt(section, marker[1]) : undefined;
      if (typeof value !== 'string' || node === doc.activeElement) return;
      if (node.dataset.tuxcmsMultiline) {
        if (tidyLines(node.innerText) !== tidyLines(value)) node.innerText = value;
      } else if (normalise(node.textContent) !== normalise(value)) {
        node.textContent = value;
      }
    });
  }, [sectionsById]);

  // The load event carries the frame that fired it, so the preview is prepared
  // from that element rather than reached for through the ref.
  const preparePreview = (event) => {
    const frame = event.currentTarget;
    try {
      const doc = frame.contentDocument;
      if (doc?.body) {
        const style = doc.querySelector('style[data-tuxcms-preview]') || doc.createElement('style');
        style.dataset.tuxcmsPreview = 'true';
        style.textContent = `
          .wow { visibility: visible !important; animation: none !important; }
          .has-motion [data-reveal] { opacity: 1 !important; transform: none !important; }
          /* Everything that can be edited carries a resting dashed line, so the
             editable parts of the page are visible without hunting for them.
             The line is blue because almost no site is, which keeps it easy
             to spot on any theme. Hover firms it, focus makes it heavier. */
          [data-tuxcms-editable] { cursor: text; outline: 1.5px dashed rgba(37, 99, 235, .7); outline-offset: 5px; }
          [data-tuxcms-editable]:hover { outline: 1.5px solid rgb(37, 99, 235); }
          [data-tuxcms-editable]:focus { outline: 2px solid rgb(37, 99, 235); }
          [data-tuxcms-image-editable] { cursor: pointer; outline: 1.5px dashed rgba(37, 99, 235, .7); outline-offset: 5px; }
          [data-tuxcms-image-editable]:hover { outline: 2px solid rgb(37, 99, 235); }
          /* A lighter blue where the line falls on a dark band. The resting
             rule stands aside for hover and focus, which set their own colour. */
          [data-tuxcms-on-dark][data-tuxcms-editable]:not(:hover):not(:focus) { outline-color: rgba(125, 185, 255, .85); }
          [data-tuxcms-on-dark][data-tuxcms-image-editable]:not(:hover) { outline-color: rgba(125, 185, 255, .85); }
          [data-tuxcms-on-dark][data-tuxcms-editable]:hover { outline-color: rgb(160, 205, 255); }
          [data-tuxcms-on-dark][data-tuxcms-editable]:focus { outline-color: rgb(160, 205, 255); }
          [data-tuxcms-on-dark][data-tuxcms-image-editable]:hover { outline-color: rgb(160, 205, 255); }
          .tuxcms-photo-button { position: absolute; z-index: 2147483000; margin: 0; padding: 7px 12px; border: 0; border-radius: 6px; background: #111; color: #fff; font: 600 12px/1 system-ui, sans-serif; letter-spacing: 0; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, .35); }
          .tuxcms-photo-button:hover, .tuxcms-photo-button:focus-visible { background: #fff; color: #111; outline: 2px solid #111; }
        `;
        if (!style.isConnected) doc.head.appendChild(style);

        // Make one marked element edit the field it names.
        const makeMarkedEditable = (element, section, path) => {
          if (element.dataset.tuxcmsEditable === 'true') return;
          const stored = valueAt(section, path);
          const multiline = isMultiline(element, stored);
          const read = () => (multiline ? tidyLines(element.innerText) : normalise(element.textContent));
          const initial = read();

          element.dataset.tuxcmsEditable = 'true';
          element.dataset.tuxcmsFieldKey = `marker:${section.id}:${path}`;
          if (multiline) element.dataset.tuxcmsMultiline = 'true';
          // Plain text only, so pasting cannot carry styling into the site;
          // the older value where a browser does not know it.
          element.contentEditable = 'plaintext-only';
          if (element.contentEditable !== 'plaintext-only') element.contentEditable = 'true';
          element.setAttribute('role', 'textbox');
          element.setAttribute('aria-label', `Edit ${element.tagName.toLowerCase()} text`);
          element.setAttribute('aria-multiline', multiline ? 'true' : 'false');
          element.spellcheck = true;

          element.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            onSelect?.(section._uid);
          });
          element.addEventListener('keydown', (event) => {
            // One line: Enter finishes. Several lines: Enter starts a new one.
            if (event.key === 'Enter' && !multiline) {
              event.preventDefault();
              element.blur();
            }
          });
          element.addEventListener('blur', () => {
            const value = read();
            if (!value || value === initial) return;
            onEdit?.(section._uid, path, value);
          });
        };

        const installEditors = () => {
          // Some themes animate a heading a character at a time: a script
          // splits its words into one element per letter, with a hidden copy
          // of the words beside it for screen readers. The preview does not
          // animate, so put each such heading back to one plain line of text,
          // which is what an editor expects to click into and type in.
          doc.querySelectorAll('[data-splitting="chars"]').forEach((element) => {
            const accessibilityClone = element.previousElementSibling;
            if (accessibilityClone?.classList.contains('visually-hidden')) {
              accessibilityClone.remove();
            }
            element.removeAttribute('aria-hidden');
            element.removeAttribute('data-splitting');
            element.textContent = normalise(element.textContent);
          });

          // Each marked element edits exactly the field it names.
          doc.querySelectorAll('[data-tuxcms-field]').forEach((element) => {
            const marker = parseMarker(element.dataset.tuxcmsField);
            const section = marker && sectionsById.get(marker[0]);
            if (section) makeMarkedEditable(element, section, marker[1]);
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

        // Each marked picture opens the picker for exactly the field it names.
        doc.querySelectorAll('[data-tuxcms-image]').forEach((element) => {
          const marker = parseMarker(element.dataset.tuxcmsImage);
          const section = marker && sectionsById.get(marker[0]);
          if (!section) return;
          const [, path] = marker;
          // The description sits beside the picture, image and image_alt, and
          // is saved there even if the field does not exist yet.
          const parts = path.split('.');
          const key = parts.pop();
          const parentPath = parts.join('.');
          const parent = parentPath ? valueAt(section, parentPath) : section;
          const altKey = key === 'image' ? 'image_alt' : `${key}_alt`;
          const image = {
            sectionUid: section._uid,
            field: path,
            altField: parentPath ? `${parentPath}.${altKey}` : null,
            url: valueAt(section, path) || element.getAttribute('src') || '',
            alt: (parent && parent[altKey]) || element.getAttribute('alt') || '',
          };
          element.dataset.tuxcmsImageEditable = 'true';
          element.title = 'Click to replace image';
          element.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            onSelect?.(section._uid);
            onImageEdit?.(image, element);
          });
          addPhotoButton(element, () => {
            onSelect?.(section._uid);
            onImageEdit?.(image, element);
          });
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
          // A page rendered into the frame has no address of its own, so
          // compare against the address its links resolve from.
          if (url.origin !== new URL(doc.baseURI).origin) return;
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

          // The resting lines are drawn before anything is hovered, so every
          // editable region is judged up front as well as on arrival. It is
          // kept on the preview window so each later pass can run it again,
          // and repeated once the page's images and fonts have settled.
          doc.defaultView.tuxcmsJudgeAll = () => {
            doc.querySelectorAll('[data-tuxcms-editable], [data-tuxcms-image-editable]').forEach((element) => {
              element.toggleAttribute('data-tuxcms-on-dark', onDark(element));
            });
          };
          doc.defaultView.addEventListener('load', () => doc.defaultView.tuxcmsJudgeAll());
        }

        // Mark the editable regions, then judge each one against what it sits
        // on, in that order: the lines are only drawn once the marks exist.
        const settle = () => {
          installEditors();
          doc.defaultView.tuxcmsJudgeAll?.();
        };
        settle();
        const view = frame.contentWindow;
        view?.requestAnimationFrame(() => view.requestAnimationFrame(settle));
      }
    } catch {
      // A custom cross-origin preview still works, without the editor override.
    }
  };

  return (
    <iframe
      ref={frameRef}
      key={`${revision}:${pages.length}:${sections.length}`}
      title="Website preview"
      // The page as the CMS renders it for the editor, with its field markers.
      srcDoc={html}
      onLoad={preparePreview}
      className="block w-full border-0 bg-white"
      style={{ height: '100%', minWidth: mobile ? 390 : 960 }}
    />
  );
}

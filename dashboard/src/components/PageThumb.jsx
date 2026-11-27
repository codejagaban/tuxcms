import { useEffect, useRef, useState } from 'react';

// The width a thumbnail's page is laid out at before it is scaled down, so
// the picture is of the desktop page and not a squeezed one.
const PAGE_WIDTH = 1280;
const PAGE_HEIGHT = 800;

/**
 * A small live picture of a page, for the gallery.
 *
 * It is the real built page in a frame, scaled to fit. Scripts are switched
 * off inside it, so a wall of thumbnails costs little and nothing in them
 * moves. A page that is not on the site yet has no built page to show, and
 * gets its title set large instead.
 */
export default function PageThumb({ page }) {
  const box = useRef(null);
  const [scale, setScale] = useState(0);

  useEffect(() => {
    const node = box.current;
    if (!node) return undefined;
    const fit = () => setScale(node.clientWidth / PAGE_WIDTH);
    const observer = new ResizeObserver(fit);
    observer.observe(node);
    return () => observer.disconnect();
  }, []);

  const live = page.status === 'published' && !page.is_scheduled;
  const path = page.is_homepage ? '/' : `/${String(page.path || page.slug || '').replace(/^\/+|\/+$/g, '')}/`;

  return (
    <div
      ref={box}
      className="relative overflow-hidden bg-[var(--color-paper-3)]"
      style={{ aspectRatio: `${PAGE_WIDTH} / ${PAGE_HEIGHT}` }}
    >
      {live && scale > 0 && (
        <iframe
          title=""
          aria-hidden="true"
          tabIndex={-1}
          loading="lazy"
          sandbox="allow-same-origin"
          src={`${path}?tuxcms_preview=thumb`}
          className="pointer-events-none absolute left-0 top-0 origin-top-left border-0 bg-white"
          style={{ width: PAGE_WIDTH, height: PAGE_HEIGHT, transform: `scale(${scale})` }}
        />
      )}
      {!live && (
        <div className="absolute inset-0 grid place-items-center p-5 text-center">
          <div>
            <div className="text-xl font-semibold tracking-[-0.03em] text-[var(--color-ink-2)]">{page.title}</div>
            <div className="mt-1.5 text-xs text-[var(--color-muted)]">Not on the site yet</div>
          </div>
        </div>
      )}
    </div>
  );
}

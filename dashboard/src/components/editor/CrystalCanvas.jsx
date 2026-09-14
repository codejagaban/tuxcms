import { useRef } from 'react';

/** Render the generated Crystal page itself so the editor cannot drift. */
export default function CrystalCanvas({ path, revision = 0, mobile = false }) {
  const frameRef = useRef(null);
  const separator = path.includes('?') ? '&' : '?';
  const src = `${path}${separator}tuxcms_preview=${revision}`;

  const preparePreview = () => {
    try {
      const doc = frameRef.current?.contentDocument;
      if (doc?.body) {
        const style = doc.createElement('style');
        style.dataset.tuxcmsPreview = 'true';
        style.textContent = '.wow { visibility: visible !important; animation: none !important; }';
        doc.head.appendChild(style);
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
      style={{ height: mobile ? 760 : 820, minWidth: mobile ? 390 : 960 }}
    />
  );
}

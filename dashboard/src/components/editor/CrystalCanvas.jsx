import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

const documentMarkup = `<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/themes/crystal/css/bootstrap.min.css">
    <link rel="stylesheet" href="/themes/crystal/css/style.css?v=3">
    <link rel="stylesheet" href="/themes/crystal/css/style-responsive.css">
    <link rel="stylesheet" href="/themes/crystal/css/vertical-rhythm.min.css">
    <link rel="stylesheet" href="/themes/crystal/css/demo-fancy/demo-fancy.css?v=2">
    <style>
      html, body { margin: 0; overflow: hidden; background: #fff; }
      body { min-height: 0; }
      .min-height-100vh { min-height: 760px !important; }
      .wow { visibility: visible !important; animation: none !important; }
      .tux-section { position: relative; }
      .tux-section::after { content: ""; position: absolute; inset: 0; z-index: 50; pointer-events: none; transition: box-shadow 160ms ease; }
      .tux-section:hover::after { box-shadow: inset 0 0 0 1px #111; }
      .tux-section.is-selected::after { box-shadow: inset 0 0 0 2px #111; }
      .tux-section.is-hidden { opacity: .4; }
      [contenteditable="true"] { cursor: text; border-radius: 2px; }
      [contenteditable="true"]:focus { outline: 2px solid #111; outline-offset: 4px; }
      .editor-hidden-label { position: absolute; top: 12px; right: 12px; z-index: 60; padding: 5px 8px; background: #111; color: #fff; font: 600 11px/1 system-ui, sans-serif; }
    </style>
  </head>
  <body><div id="crystal-editor-root"></div></body>
</html>`;

export default function CrystalCanvas({ children, mobile = false }) {
  const frameRef = useRef(null);
  const [mountNode, setMountNode] = useState(null);
  const [height, setHeight] = useState(720);

  useEffect(() => {
    const frame = frameRef.current;
    if (!frame) return undefined;

    const onLoad = () => {
      const doc = frame.contentDocument;
      const root = doc?.getElementById('crystal-editor-root');
      setMountNode(root || null);
      if (!doc?.body) return;

      const measure = () => setHeight(Math.max(720, doc.documentElement.scrollHeight, doc.body.scrollHeight));
      const observer = new ResizeObserver(measure);
      observer.observe(doc.body);
      measure();
      frame.__crystalObserver = observer;
    };

    frame.addEventListener('load', onLoad);
    return () => {
      frame.removeEventListener('load', onLoad);
      frame.__crystalObserver?.disconnect();
    };
  }, []);

  return (
    <>
      <iframe
        ref={frameRef}
        title="Crystal website preview"
        srcDoc={documentMarkup}
        className="block w-full border-0 bg-white"
        style={{ height, minWidth: mobile ? 390 : 960 }}
      />
      {mountNode && createPortal(children, mountNode)}
    </>
  );
}

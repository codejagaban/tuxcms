import { useEffect, useRef } from 'react';
import { animate } from 'motion';

/**
 * A figure that counts up to its value when it first appears.
 *
 * The real value is in the markup from the start, so it reads correctly with
 * motion switched off or before the script has run.
 */
export default function Count({ value }) {
  const node = useRef(null);

  useEffect(() => {
    const element = node.current;
    if (!element || !Number.isFinite(value) || value <= 0) return undefined;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return undefined;

    const controls = animate(0, value, {
      duration: 0.9,
      ease: [0.16, 1, 0.3, 1],
      onUpdate: (latest) => { element.textContent = String(Math.round(latest)); },
      onComplete: () => { element.textContent = String(value); },
    });
    return () => controls.stop();
  }, [value]);

  return <span ref={node}>{value}</span>;
}

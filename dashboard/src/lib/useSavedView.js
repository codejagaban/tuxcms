import { useState } from 'react';

/**
 * The choice between a list and a gallery, remembered between visits under
 * `storageKey`. A private window may refuse to remember, and then it is
 * simply the list each time.
 */
export function useSavedView(storageKey) {
  const [view, setView] = useState(() => {
    try {
      return localStorage.getItem(storageKey) === 'gallery' ? 'gallery' : 'list';
    } catch {
      return 'list';
    }
  });

  const chooseView = (next) => {
    setView(next);
    try {
      localStorage.setItem(storageKey, next);
    } catch {
      // Nothing to do: the choice lasts for this visit.
    }
  };

  return [view, chooseView];
}

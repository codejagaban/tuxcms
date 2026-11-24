import { useEffect, useState } from 'react';

// The appearance choice: 'light', 'dark', or 'system' (follow the device).
// index.html applies it before the first paint; this keeps it in step after.
const KEY = 'tux_theme';
const CHOICES = ['light', 'dark', 'system'];
const device = () => window.matchMedia('(prefers-color-scheme: dark)');
const listeners = new Set();

export const getAppearance = () => {
  try {
    const stored = localStorage.getItem(KEY);
    return CHOICES.includes(stored) ? stored : 'system';
  } catch {
    return 'system';
  }
};

const apply = (choice = getAppearance()) => {
  const dark = choice === 'dark' || (choice === 'system' && device().matches);
  document.documentElement.dataset.theme = dark ? 'dark' : 'light';
};

export const setAppearance = (choice) => {
  try {
    if (choice === 'system') localStorage.removeItem(KEY);
    else localStorage.setItem(KEY, choice);
  } catch {
    // Private windows may refuse storage; the choice then lasts for this visit.
  }
  apply(choice);
  listeners.forEach((listener) => listener(choice));
};

// Follow the device while on 'system', and a choice made in another tab.
device().addEventListener('change', () => apply());
window.addEventListener('storage', (event) => {
  if (event.key !== KEY) return;
  apply();
  listeners.forEach((listener) => listener(getAppearance()));
});

export const useAppearance = () => {
  const [choice, setChoice] = useState(getAppearance);

  useEffect(() => {
    listeners.add(setChoice);
    return () => listeners.delete(setChoice);
  }, []);

  return [choice, setAppearance];
};

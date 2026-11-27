/*
 * Motion for the Shopfront theme, written with Motion (motion.dev).
 *
 * Everything on the page is visible and in its final place without this file.
 * It only moves things that are already there: nothing starts hidden, so a
 * script that fails to load, or never runs, costs the visitor nothing.
 *
 * Built into public/themes/shopfront/js/site.js with `npm run build:theme`.
 */
import { animate, inView, scroll, stagger } from 'motion';

const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
// Inside the dashboard's live preview the page is being edited: keep it still.
const editing = new URLSearchParams(window.location.search).has('tuxcms_preview')
  || document.documentElement.hasAttribute('data-tuxcms-preview');

if (!calm && !editing) {
  const settle = { type: 'spring', stiffness: 120, damping: 20 };

  // The sign arrives: the board settles into place and its title follows.
  const board = document.querySelector('.sign__board');
  if (board) {
    animate(board, { y: [28, 0] }, settle);
    const title = board.querySelector('.sign__title');
    // Only its position: changing the spacing would re-wrap the line mid-move.
    if (title) animate(title, { y: [36, 0] }, { ...settle, delay: 0.08 });
    const footing = document.querySelectorAll('.sign__footing > *');
    if (footing.length) animate(footing, { y: [22, 0] }, { ...settle, delay: stagger(0.08, { startDelay: 0.18 }) });
  }

  // The fascia steps out of the way going down the page and returns going up.
  const fascia = document.querySelector('.fascia');
  if (fascia) {
    let last = window.scrollY;
    let hidden = false;
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      const open = fascia.querySelector('details[open]');
      const hide = y > last && y > 320 && !open;
      if (hide !== hidden && Math.abs(y - last) > 6) {
        hidden = hide;
        animate(fascia, { y: hide ? '-130%' : '0%' }, { type: 'spring', stiffness: 260, damping: 32 });
      }
      last = y;
    }, { passive: true });
  }

  // Headings draw in from the left as their section arrives.
  document.querySelectorAll('.head, .hang__title').forEach((heading) => {
    inView(heading, () => {
      animate(heading, { x: [-28, 0] }, settle);
    }, { amount: 0.4 });
  });

  // Lists arrive one item after another.
  const groups = ['.trades > *', '.works > *', '.tally > *', '.words > *', '.staff > *', '.asks > *', '.roles > *', '.reach > *'];
  groups.forEach((selector) => {
    const items = document.querySelectorAll(selector);
    if (!items.length) return;
    inView(items[0].parentElement, () => {
      animate(items, { y: [26, 0] }, { ...settle, delay: stagger(0.07) });
    }, { amount: 0.15 });
  });

  // Figures count up to their value, keeping whatever surrounds the number.
  document.querySelectorAll('.tally__n').forEach((figure) => {
    const text = figure.textContent.trim();
    const match = text.match(/^(\D*)(\d[\d,]*(?:\.\d+)?)(.*)$/);
    if (!match) return;
    const [, before, digits, after] = match;
    const target = parseFloat(digits.replace(/,/g, ''));
    const decimals = (digits.split('.')[1] || '').length;
    const grouped = digits.includes(',');
    const show = (value) => {
      const fixed = value.toFixed(decimals);
      const shown = grouped ? Number(fixed).toLocaleString('en-GB', { minimumFractionDigits: decimals }) : fixed;
      figure.textContent = before + shown + after;
    };
    inView(figure, () => {
      animate(0, target, { duration: 1.4, ease: [0.16, 1, 0.3, 1], onUpdate: show, onComplete: () => { figure.textContent = text; } });
    }, { amount: 0.6 });
  });

  // Photographs drift inside their frames as the page moves past them.
  document.querySelectorAll('.frame img').forEach((image) => {
    scroll(animate(image, { y: ['-7%', '7%'] }, { ease: 'linear' }), {
      target: image.parentElement,
      offset: ['start end', 'end start'],
    });
  });
  const photo = document.querySelector('.sign__photo');
  if (photo) {
    scroll(animate(photo, { y: ['0%', '18%'], scale: [1, 1.08] }, { ease: 'linear' }), {
      target: photo.parentElement,
      offset: ['start start', 'end start'],
    });
  }

  // The name on the floor slides across as the page comes to rest on it.
  const name = document.querySelector('.foot__name');
  if (name) {
    scroll(animate(name, { x: ['9%', '0%'] }, { ease: 'linear' }), {
      target: name,
      offset: ['start end', 'end end'],
    });
  }
}

/*
 * Motion for the Shopfront theme, written with Motion (motion.dev).
 *
 * Everything on the page is visible and in its final place without this file.
 * It only moves things that are already there: nothing starts hidden, so a
 * script that fails to load, or never runs, costs the visitor nothing.
 *
 * It also brings the front page's copy of the editor to life: its words can
 * be typed over, its device buttons resize the preview, and Publish runs a
 * build. Nothing is saved anywhere.
 *
 * Built into public/themes/shopfront/js/site.js with `npm run build:theme`.
 */
import { animate, inView, scroll, stagger } from 'motion';

const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
// Inside the dashboard's live preview the page is being edited: keep it still,
// and leave the editor copy alone so it cannot take the real editor's clicks.
const editing = new URLSearchParams(window.location.search).has('tuxcms_preview')
  || document.documentElement.hasAttribute('data-tuxcms-preview');

const settle = { type: 'spring', stiffness: 120, damping: 20 };
const move = (element, keyframes, options) => (calm ? null : animate(element, keyframes, options));

/* ── The editor ─────────────────────────────────────────── */
function studio(root) {
  const state = root.querySelector('[data-state]');
  const stateText = root.querySelector('[data-state-text]');
  const publish = root.querySelector('[data-publish]');
  const publishText = root.querySelector('[data-publish-text]');
  const log = root.querySelector('[data-log]');
  const canvas = root.querySelector('[data-canvas]');
  const pages = Number(root.dataset.pages) || 1;
  const fields = [...root.querySelectorAll('[data-edit]')];
  const original = new Map(fields.map((field) => [field, field.textContent]));
  let building = false;

  root.classList.add('is-live');
  log.textContent = 'Click any outlined words in the preview and type over them.';

  const changed = () => fields.filter((field) => field.textContent !== original.get(field)).length;
  const showState = () => {
    const count = changed();
    state.classList.toggle('is-pending', count > 0);
    stateText.textContent = count ? `${count} ${count === 1 ? 'change' : 'changes'} to publish` : 'Published';
  };

  fields.forEach((field) => {
    // Plain text only, where the browser can; otherwise ordinary editing.
    try { field.contentEditable = 'plaintext-only'; } catch { field.contentEditable = 'true'; }
    field.setAttribute('spellcheck', 'false');
    field.setAttribute('role', 'textbox');
    field.setAttribute('aria-label', `${field.dataset.label} in the preview`);
    field.addEventListener('input', () => {
      showState();
      log.classList.remove('is-done');
      log.textContent = `Editing ${field.dataset.label.toLowerCase()}. Press Publish when you are happy.`;
    });
    field.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') { event.preventDefault(); field.blur(); }
    });
  });

  root.querySelectorAll('[data-view]').forEach((button) => {
    button.addEventListener('click', () => {
      root.querySelectorAll('[data-view]').forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
      canvas.classList.toggle('is-phone', button.dataset.view === 'phone');
    });
  });

  publish.addEventListener('click', () => {
    if (building) return;
    building = true;
    const count = changed();
    publishText.textContent = 'Publishing';
    log.classList.remove('is-done');
    log.textContent = `Building ${pages} ${pages === 1 ? 'page' : 'pages'}`;
    const finish = () => {
      building = false;
      fields.forEach((field) => original.set(field, field.textContent));
      showState();
      publishText.textContent = 'Publish';
      publish.style.setProperty('--done', '0%');
      log.classList.add('is-done');
      log.textContent = `Published ${pages} ${pages === 1 ? 'page' : 'pages'}${count ? ` with your ${count === 1 ? 'change' : 'changes'}` : ''}. In the real dashboard that is live now; this copy keeps nothing.`;
    };
    if (calm) {
      finish();
      return;
    }
    animate(0, 100, {
      duration: 1.1,
      ease: [0.3, 0.1, 0.2, 1],
      onUpdate: (value) => publish.style.setProperty('--done', `${value}%`),
      onComplete: finish,
    });
  });

  // The sections list jumps to its section on this page.
  root.querySelectorAll('.studio__sections a').forEach((link) => {
    link.addEventListener('click', () => {
      root.querySelectorAll('.studio__sections a').forEach((other) => other.classList.toggle('is-here', other === link));
    });
  });
}

if (!editing) {
  document.querySelectorAll('[data-studio]').forEach(studio);
}

if (!calm && !editing) {
  // The hero arrives: its title settles into place and the rest follows.
  const hero = document.querySelector('.hero');
  if (hero) {
    const title = hero.querySelector('.hero__title');
    // Only its position: changing the spacing would re-wrap the line mid-move.
    if (title) animate(title, { y: [40, 0] }, { ...settle, delay: 0.05 });
    const rest = hero.querySelectorAll('.hero__aside > *');
    if (rest.length) animate(rest, { y: [22, 0] }, { ...settle, delay: stagger(0.07, { startDelay: 0.15 }) });
    const desk = hero.querySelector('.studio');
    if (desk) {
      // As the page scrolls it slides over the stage a little faster than
      // the page, so it reads as lifted above it.
      scroll(animate(desk, { y: [0, -40] }, { ease: 'linear' }), {
        target: desk,
        offset: ['start 60%', 'end start'],
      });
    }
  }

  // The bar steps out of the way going down the page and returns going up.
  const bar = document.querySelector('.bar');
  if (bar) {
    let last = window.scrollY;
    let hidden = false;
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      const open = bar.querySelector('details[open]');
      const hide = y > last && y > 400 && !open;
      if (hide !== hidden && Math.abs(y - last) > 6) {
        hidden = hide;
        animate(bar, { y: hide ? '-110%' : '0%' }, { type: 'spring', stiffness: 260, damping: 32 });
      }
      last = y;
    }, { passive: true });
  }

  // Headings rise as their section arrives.
  document.querySelectorAll('.head, .call__title').forEach((heading) => {
    inView(heading, () => {
      animate(heading, { y: [28, 0] }, settle);
    }, { amount: 0.4 });
  });

  // Lists arrive one item after another.
  const groups = ['.tiles > *', '.wall > *', '.tally__list > *', '.words > *', '.staff > *', '.asks > *', '.roles > *', '.reach > *'];
  groups.forEach((selector) => {
    const items = document.querySelectorAll(selector);
    if (!items.length) return;
    inView(items[0].parentElement, () => {
      animate(items, { y: [30, 0] }, { ...settle, delay: stagger(0.07) });
    }, { amount: 0.15 });
  });

  // Figures count up to their value, keeping whatever surrounds the number.
  document.querySelectorAll('.tally__n').forEach((figure) => {
    const text = figure.textContent.trim();
    const match = text.match(/^(\D*)(\d[\d,]*(?:\.\d+)?)(.*)$/);
    if (!match) return;
    const [, before, digits, after] = match;
    const target = parseFloat(digits.replace(/,/g, ''));
    if (!target) return;
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
    scroll(move(image, { y: ['-5%', '5%'] }, { ease: 'linear' }), {
      target: image.parentElement,
      offset: ['start end', 'end start'],
    });
  });
}

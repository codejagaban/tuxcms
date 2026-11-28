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

// The showcase: choosing a feature opens its piece of the product. It moves
// on to the next by itself while it is on screen, and holds while it is
// pointed at or used. In the dashboard's preview it never moves on.
document.querySelectorAll('[data-show]').forEach((show) => {
  const items = [...show.querySelectorAll('.show__item')];
  const tabs = [...show.querySelectorAll('[data-show-tab]')];
  const panels = [...show.querySelectorAll('[data-show-panel]')];
  let current = 0;
  let timer = null;
  let held = false;
  let seen = false;

  const select = (index, fromUser) => {
    current = (index + items.length) % items.length;
    items.forEach((item, i) => {
      item.classList.toggle('is-on', i === current);
      item.style.setProperty('--done', '0%');
    });
    tabs.forEach((tab, i) => {
      tab.setAttribute('aria-selected', String(i === current));
      tab.tabIndex = i === current ? 0 : -1;
    });
    panels.forEach((panel, i) => { panel.hidden = i !== current; });
    if (!calm && !editing) {
      animate(panels[current], { y: [12, 0], filter: ['blur(6px)', 'blur(0px)'] }, { duration: 0.45, ease: [0.2, 0.8, 0.2, 1] });
    }
    if (fromUser) held = true;
    run();
  };

  const run = () => {
    timer?.stop();
    timer = null;
    if (calm || editing || held || !seen || items.length < 2) {
      show.classList.remove('is-running');
      return;
    }
    show.classList.add('is-running');
    const item = items[current];
    timer = animate(0, 100, {
      duration: 6,
      ease: 'linear',
      onUpdate: (value) => item.style.setProperty('--done', `${value}%`),
      onComplete: () => select(current + 1, false),
    });
  };

  tabs.forEach((tab, i) => {
    tab.addEventListener('click', () => select(i, true));
    tab.addEventListener('keydown', (event) => {
      const step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[event.key];
      if (!step) return;
      event.preventDefault();
      select(i + step, true);
      tabs[current].focus();
    });
  });
  show.addEventListener('pointerenter', () => { held = true; run(); });
  show.addEventListener('pointerleave', () => { held = false; run(); });
  inView(show, () => {
    seen = true;
    run();
    return () => { seen = false; run(); };
  }, { amount: 0.4 });
  tabs.forEach((tab, i) => { tab.tabIndex = i === current ? 0 : -1; });
});

// The front page's scene plays only for visitors who welcome motion, and
// never in the dashboard's preview. Otherwise its still frame stays.
const film = document.querySelector('.hero__film');
if (film && !calm && !editing) {
  film.play().catch(() => {});
}

if (!calm && !editing) {
  // The hero rises out of a blur in CSS as the page opens. Here the editor
  // only drifts as the page scrolls.
  const hero = document.querySelector('.hero');
  if (hero) {
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

  // Headings rise as their section arrives.
  document.querySelectorAll('.head, .call__glass').forEach((heading) => {
    inView(heading, () => {
      animate(heading, { y: [24, 0], filter: ['blur(6px)', 'blur(0px)'] }, { ...settle, filter: { duration: 0.5, ease: 'easeOut' } });
    }, { amount: 0.4 });
  });

  // Lists arrive one item after another.
  const groups = ['.bento > *', '.bit__log > *', '.bit__stack > *', '.tally__list > *', '.words > *', '.staff > *', '.asks > *', '.roles > *', '.reach > *'];
  groups.forEach((selector) => {
    const items = document.querySelectorAll(selector);
    if (!items.length) return;
    inView(items[0].parentElement, () => {
      // Each rises out of a blur, as the dashboard's screens arrive.
      animate(items, { y: [28, 0], filter: ['blur(6px)', 'blur(0px)'] }, { ...settle, delay: stagger(0.07), filter: { duration: 0.5, ease: 'easeOut', delay: stagger(0.07) } });
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

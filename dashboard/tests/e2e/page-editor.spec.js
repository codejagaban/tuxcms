import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const pageRecord = (id, title, slug, isHomepage = false) => ({
  id,
  title,
  slug,
  path: isHomepage ? '/' : `/${slug}`,
  status: 'published',
  template: 'shopfront',
  is_homepage: isHomepage,
  show_in_nav: true,
  nav_label: title,
  excerpt: '',
  content: '',
  seo: {},
  sections: [{
    id: id * 10,
    key: 'hero',
    type: 'hero',
    title: `${title} heading`,
    content: `${title} supporting text`,
    data: { image: '/old.jpg', image_alt: 'Old image' },
    order: 0,
    is_visible: true,
  }],
});

// A page whose two sections carry the same heading. Its preview is marked
// up, so the editor must tell them apart by section, not by their words.
const twinRecord = () => ({
  ...pageRecord(3, 'Twins', 'twins'),
  twins: true,
  sections: [31, 32].map((id, order) => ({
    id, key: `text-${order}`, type: 'text', title: 'Same heading', content: '', data: {}, order, is_visible: true,
  })),
});

// The page as the editor's preview endpoint renders it, with field markers
// as a marked-up theme writes them. The twins page shows its sections in the
// opposite order to the list, so anything pairing headings by their words
// would pair each with the wrong section.
const previewBody = (record) => {
  if (record.twins) {
    return `<!doctype html><html data-tuxcms-preview><head></head><body><main>${[...record.sections].reverse()
      .map((section) => `<section data-tuxcms-section="${section.id}"><h2 data-tuxcms-field="${section.id}:title">${section.title}</h2></section>`)
      .join('')}</main></body></html>`;
  }
  // The heading is split a letter at a time, as an animated theme leaves it,
  // with the hidden copy for screen readers beside it.
  const section = record.sections[0];
  const id = section.id;
  return `<!doctype html><html data-tuxcms-preview><head></head><body><nav><a href="/">Home</a><a href="/about/">About</a></nav><main><section data-tuxcms-section="${id}"><h1><span class="visually-hidden">${section.title}</span><span data-splitting="chars" aria-hidden="true" data-tuxcms-field="${id}:title">${[...section.title].map((letter) => `<span class="char">${letter}</span>`).join('')}</span></h1><p data-tuxcms-field="${id}:content">${section.content}</p><img src="${section.data.image}" alt="${section.data.image_alt}" data-tuxcms-image="${id}:data.image"></section></main></body></html>`;
};

async function mockEditor(page) {
  const records = {
    1: pageRecord(1, 'Home', 'home', true),
    2: pageRecord(2, 'About', 'about'),
    3: twinRecord(),
  };
  const saves = [];

  await page.addInitScript(() => {
    localStorage.setItem('auth_token', 'editor-token');
  });

  await page.route('**/api/v1/**', async (route) => {
    const request = route.request();
    const url = new URL(request.url());
    const path = url.pathname;

    if (path.endsWith('/auth/me')) {
      return route.fulfill({ json: { data: { id: 1, name: 'Editor', email: 'editor@example.com' } } });
    }
    if (path.endsWith('/pages') && request.method() === 'GET') {
      return route.fulfill({ json: { data: Object.values(records) } });
    }
    if (path.endsWith('/pages/1/revisions') && request.method() === 'GET') {
      return route.fulfill({ json: { data: [{
        id: 1,
        created_at: '2026-09-16T09:30:00Z',
        author: 'Editor',
        snapshot: { ...records[1], title: 'Earlier Home', sections: [{ ...records[1].sections[0], title: 'Earlier heading' }] },
      }] } });
    }
    const previewMatch = path.match(/\/pages\/(\d+)\/preview$/);
    if (previewMatch && request.method() === 'GET') {
      return route.fulfill({ contentType: 'text/html', body: previewBody(records[previewMatch[1]]) });
    }
    const pageMatch = path.match(/\/pages\/(\d+)$/);
    if (pageMatch && request.method() === 'GET') {
      return route.fulfill({ json: { data: records[pageMatch[1]] } });
    }
    if (pageMatch && request.method() === 'PUT') {
      const payload = request.postDataJSON();
      const id = Number(pageMatch[1]);
      records[id] = {
        ...records[id],
        ...payload,
        // A new section gets an id no other section on the page already has.
        sections: payload.sections.map((section, index) => ({ ...section, id: section.id || id * 100 + index })),
      };
      saves.push(payload);
      return route.fulfill({ json: { data: records[id] } });
    }
    if (path.endsWith('/site/publish') && request.method() === 'POST') {
      return route.fulfill({ json: { message: 'Published' } });
    }
    if (path.endsWith('/media') && request.method() === 'GET') {
      return route.fulfill({ json: { data: [{ id: 9, url: '/library.jpg', file_name: 'Library image', mime_type: 'image/jpeg', custom_properties: { alt_text: 'Library alt' } }] } });
    }
    return route.fulfill({ status: 404, json: { message: 'Unhandled test request' } });
  });

  return { records, saves };
}

test('inline heading remains single and editable after publishing', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  const heading = preview.locator('h1 [data-tuxcms-editable], h1[data-tuxcms-editable]').first();
  await expect(heading).toHaveText('Home heading');
  await expect(heading).toHaveAttribute('contenteditable', /^(plaintext-only|true)$/);

  await heading.fill('Edited heading');
  await heading.press('Tab');
  await expect(page.locator('[title="Unsaved changes"]')).toBeVisible();
  await page.getByRole('button', { name: 'Publish' }).click();

  await expect.poll(() => saves.at(-1)?.sections[0].title).toBe('Edited heading');
  await expect(heading).toHaveText('Edited heading');
  await expect(heading).toHaveAttribute('contenteditable', /^(plaintext-only|true)$/);

  await heading.fill('Edited again');
  await heading.press('Tab');
  await expect(heading).toHaveText('Edited again');
});

test('image replacement uses the media library and persists accessible text', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  await preview.locator('img[alt="Old image"]').click();

  const dialog = page.getByRole('dialog');
  await expect(dialog).toContainText('Choose image');
  await dialog.getByText('Library image', { exact: true }).click();
  await dialog.getByLabel('Alternative text').fill('Updated accessible description');
  await dialog.getByRole('button', { name: 'Use image' }).click();
  await page.getByRole('button', { name: 'Publish' }).click();

  await expect.poll(() => saves.at(-1)?.sections[0].data.image).toBe('/library.jpg');
  await expect.poll(() => saves.at(-1)?.sections[0].data.image_alt).toBe('Updated accessible description');
});

test('preview navigation opens the destination in the editor', async ({ page }) => {
  await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  await preview.getByRole('link', { name: 'About' }).click();

  await expect(page).toHaveURL(/\/dashboard\/pages\/2\/edit$/);
  await expect(page.getByText('About', { exact: true }).first()).toBeVisible();
});

test('revision history restores an earlier state into the editable preview', async ({ page }) => {
  await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  await page.getByRole('button', { name: 'Page history' }).click();
  const dialog = page.getByRole('dialog');
  await expect(page.getByRole('button', { name: 'Close dialog' })).toBeFocused();
  await expect(dialog).toContainText('Earlier Home');
  await dialog.getByRole('button', { name: 'Restore' }).click();

  const preview = page.frameLocator('iframe[title="Website preview"]');
  await expect(preview.locator('[data-tuxcms-editable]').filter({ hasText: 'Earlier heading' })).toHaveCount(1);
  await expect(page.locator('[title="Unsaved changes"]')).toBeVisible();
});

test('revision dialog closes with Escape and restores focus', async ({ page }) => {
  await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const historyButton = page.getByRole('button', { name: 'Page history' });
  await historyButton.click();
  await page.keyboard.press('Escape');

  await expect(page.getByRole('dialog')).toHaveCount(0);
  await expect(historyButton).toBeFocused();
});

test('editor starts with a mobile-sized preview on narrow screens', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const frame = page.locator('iframe[title="Website preview"]');
  await expect(frame).toBeVisible();
  const box = await frame.boundingBox();
  expect(box.width).toBeLessThanOrEqual(390);
  await expect(page.getByRole('tab', { name: 'Page' })).toBeVisible();
});

test('dirty edits autosave without publishing', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  const heading = preview.locator('h1 [data-tuxcms-editable], h1[data-tuxcms-editable]').first();
  await heading.fill('Autosaved heading');
  await heading.press('Tab');

  await expect(page.locator('[title="Unsaved changes"]')).toBeVisible();
  await expect.poll(() => saves.at(-1)?.sections[0].title, { timeout: 10000 }).toBe('Autosaved heading');
  await expect(page.locator('[title="Unsaved changes"]')).toHaveCount(0);
});

test('editor shell has no serious automated accessibility violations', async ({ page }) => {
  await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');
  await expect(page.frameLocator('iframe[title="Website preview"]').locator('main')).toBeVisible();

  const results = await new AxeBuilder({ page })
    .exclude('iframe')
    .analyze();
  const serious = results.violations.filter((violation) => ['serious', 'critical'].includes(violation.impact));
  expect(serious).toEqual([]);
});

test('marked fields with the same text are edited in their own section', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/3/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  const first = preview.locator('[data-tuxcms-field="31:title"]');
  const second = preview.locator('[data-tuxcms-field="32:title"]');
  // Found by their markers, not by matching the text.
  await expect(first).toHaveAttribute('data-tuxcms-field-key', 'marker:31:title');
  await expect(second).toHaveAttribute('data-tuxcms-field-key', 'marker:32:title');

  await second.fill('Second heading');
  await second.press('Tab');
  await page.getByRole('button', { name: 'Publish' }).click();

  await expect.poll(() => saves.at(-1)?.sections.map((section) => section.title))
    .toEqual(['Same heading', 'Second heading']);
});

test('a preview that cannot be rendered offers to try again', async ({ page }) => {
  await mockEditor(page);
  // Fails once, then works.
  let failures = 1;
  await page.route('**/api/v1/pages/1/preview', (route) => (failures-- > 0
    ? route.fulfill({ status: 500, body: 'Render failed' })
    : route.fallback()));
  await page.goto('/dashboard/pages/1/edit');

  await expect(page.getByRole('alert')).toContainText('The page could not be shown.');
  await page.getByRole('button', { name: 'Try again' }).click();

  const preview = page.frameLocator('iframe[title="Website preview"]');
  await expect(preview.locator('[data-tuxcms-field="10:title"]')).toHaveText('Home heading');
  await expect(preview.locator('[data-tuxcms-field="10:title"]')).toHaveAttribute('data-tuxcms-editable', 'true');
});

test('an element without a marker is not edited', async ({ page }) => {
  await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  // The menu shows a page title, but it is not one of this page's fields.
  const preview = page.frameLocator('iframe[title="Website preview"]');
  await expect(preview.locator('[data-tuxcms-field="10:title"]')).toHaveAttribute('data-tuxcms-editable', 'true');
  await expect(preview.locator('nav a').first()).not.toHaveAttribute('data-tuxcms-editable', 'true');
  await expect(preview.locator('[data-tuxcms-editable]')).toHaveCount(2);
});

test('an inline edit made after an autosave is kept', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/3/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  const first = preview.locator('[data-tuxcms-field="31:title"]');
  const second = preview.locator('[data-tuxcms-field="32:title"]');

  await first.fill('First, edited');
  await first.press('Tab');
  await expect.poll(() => saves.length, { timeout: 10000 }).toBe(1);

  // The save hands back the page; the second edit must still find its section.
  await second.fill('Second, edited');
  await second.press('Tab');
  await expect.poll(() => saves.at(-1)?.sections.map((section) => section.title), { timeout: 10000 })
    .toEqual(['First, edited', 'Second, edited']);
});

test('a section added in the panel appears on the page after the autosave', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/3/edit');

  const preview = page.frameLocator('iframe[title="Website preview"]');
  await expect(preview.locator('h2')).toHaveCount(2);

  await page.getByRole('button', { name: 'Add' }).click();
  await page.getByRole('button', { name: /^Text/ }).click();

  await expect.poll(() => saves.length, { timeout: 10000 }).toBe(1);
  // Rendered again from what was saved, with the new section in it.
  await expect(preview.locator('h2')).toHaveCount(3);
  await expect(preview.locator('[data-tuxcms-field="302:title"]')).toHaveAttribute('data-tuxcms-editable', 'true');
});

test('a new page asks to be saved before it can be edited on the page', async ({ page }) => {
  await mockEditor(page);
  await page.goto('/dashboard/pages/create');

  await expect(page.getByText('The page appears here once it is saved.')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Save page' })).toBeVisible();
  await expect(page.locator('iframe[title="Website preview"]')).toHaveCount(0);
});

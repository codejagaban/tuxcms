import { expect, test } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const pageRecord = (id, title, slug, isHomepage = false) => ({
  id,
  title,
  slug,
  path: isHomepage ? '/' : `/${slug}`,
  status: 'published',
  template: 'crystal',
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

async function mockEditor(page) {
  const records = {
    1: pageRecord(1, 'Home', 'home', true),
    2: pageRecord(2, 'About', 'about'),
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
        sections: payload.sections.map((section, index) => ({ ...section, id: section.id || id * 10 + index })),
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

  await page.route(/\/(?:about\/)?\?tuxcms_preview=/, async (route) => {
    const isAbout = new URL(route.request().url()).pathname.startsWith('/about');
    const record = records[isAbout ? 2 : 1];
    const section = record.sections[0];
    await route.fulfill({
      contentType: 'text/html',
      body: `<!doctype html><html><head></head><body><nav><a href="/">Home</a><a href="/about/">About</a></nav><main><h1><span class="visually-hidden">${section.title}</span><span data-splitting="chars" aria-hidden="true"><span>${section.title}</span></span></h1><p>${section.content}</p><img src="${section.data.image}" alt="${section.data.image_alt}"></main></body></html>`,
    });
  });

  return { records, saves };
}

test('inline heading remains single and editable after publishing', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Exact Crystal website preview"]');
  const heading = preview.locator('h1 [data-tuxcms-editable], h1[data-tuxcms-editable]').first();
  await expect(heading).toHaveText('Home heading');
  await expect(heading).toHaveAttribute('contenteditable', 'true');

  await heading.fill('Edited heading');
  await heading.press('Tab');
  await expect(page.locator('[title="Unsaved changes"]')).toBeVisible();
  await page.getByRole('button', { name: 'Publish' }).click();

  await expect.poll(() => saves.at(-1)?.sections[0].title).toBe('Edited heading');
  await expect(heading).toHaveText('Edited heading');
  await expect(heading).toHaveAttribute('contenteditable', 'true');

  await heading.fill('Edited again');
  await heading.press('Tab');
  await expect(heading).toHaveText('Edited again');
});

test('image replacement uses the media library and persists accessible text', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Exact Crystal website preview"]');
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

  const preview = page.frameLocator('iframe[title="Exact Crystal website preview"]');
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

  const preview = page.frameLocator('iframe[title="Exact Crystal website preview"]');
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

  const frame = page.locator('iframe[title="Exact Crystal website preview"]');
  await expect(frame).toBeVisible();
  const box = await frame.boundingBox();
  expect(box.width).toBeLessThanOrEqual(390);
  await expect(page.getByRole('tab', { name: 'Page' })).toBeVisible();
});

test('dirty edits autosave without publishing', async ({ page }) => {
  const { saves } = await mockEditor(page);
  await page.goto('/dashboard/pages/1/edit');

  const preview = page.frameLocator('iframe[title="Exact Crystal website preview"]');
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
  await expect(page.frameLocator('iframe[title="Exact Crystal website preview"]').locator('main')).toBeVisible();

  const results = await new AxeBuilder({ page })
    .exclude('iframe')
    .analyze();
  const serious = results.violations.filter((violation) => ['serious', 'critical'].includes(violation.impact));
  expect(serious).toEqual([]);
});

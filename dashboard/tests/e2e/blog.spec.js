import { expect, test } from '@playwright/test';

const post = {
  id: 7,
  title: 'Firing the new kiln',
  slug: 'firing-the-new-kiln',
  excerpt: 'It took a week.',
  body: '<p>It took a week.</p>',
  cover_image: null,
  cover_image_alt: null,
  tags: [{ name: 'Studio', slug: 'studio' }],
  author: 'Editor',
  status: 'draft',
  is_scheduled: false,
  published_at: null,
  meta_title: null,
  meta_description: null,
  path: '/blog/firing-the-new-kiln/',
  created_at: '2026-11-20T09:00:00Z',
  updated_at: '2026-11-21T09:00:00Z',
};

let saved;
let listed;
let previewed;
let created;
let updates;

test.beforeEach(async ({ page }) => {
  saved = null;
  listed = [];
  previewed = null;
  created = null;
  updates = [];
  await page.addInitScript(() => localStorage.setItem('auth_token', 'editor-token'));
  await page.route('**/api/v1/**', async (route) => {
    const request = route.request();
    const url = new URL(request.url());
    const path = url.pathname;

    if (path.endsWith('/auth/me')) {
      return route.fulfill({ json: { data: { id: 1, name: 'Editor', email: 'editor@example.com' } } });
    }
    if (path.endsWith('/site/status')) {
      return route.fulfill({ json: { data: { is_published: true } } });
    }
    if (path.endsWith('/post-tags')) {
      return route.fulfill({ json: { data: [
        { name: 'Process', slug: 'process', posts_count: 2 },
        { name: 'Studio', slug: 'studio', posts_count: 1 },
      ] } });
    }
    if (path.endsWith('/posts/preview')) {
      previewed = request.postDataJSON();
      return route.fulfill({ contentType: 'text/html', body: `<html><body><h1>${previewed.title}</h1></body></html>` });
    }
    if (path.endsWith('/posts/7') && request.method() === 'PUT') {
      saved = request.postDataJSON();
      return route.fulfill({ json: { data: { ...post, ...saved, tags: saved.tags.map((name) => ({ name, slug: name.toLowerCase() })) } } });
    }
    if (path.endsWith('/posts/7')) {
      return route.fulfill({ json: { data: post } });
    }
    if (path.endsWith('/posts') && request.method() === 'POST') {
      created = request.postDataJSON();
      return route.fulfill({ status: 201, json: { data: { ...post, id: 8, ...created, slug: 'a-brand-new-post-2', excerpt: null, tags: [] } } });
    }
    if (path.endsWith('/posts/7/revisions')) {
      return route.fulfill({ json: { data: [{
        id: 3, created_at: '2026-11-27T10:00:00Z', author: 'Editor',
        snapshot: { ...post, title: 'An earlier title', body: '<p>Earlier words.</p>', tags: ['Process'] },
      }] } });
    }
    if (path.endsWith('/posts/8') && request.method() === 'PUT') {
      updates.push(request.postDataJSON());
      return route.fulfill({ json: { data: { ...post, id: 8, ...request.postDataJSON(), slug: 'a-brand-new-post-2', tags: [] } } });
    }
    if (path.endsWith('/posts/8')) {
      return route.fulfill({ json: { data: { ...post, id: 8, title: 'A brand new post', slug: 'a-brand-new-post-2', tags: [] } } });
    }
    if (path.endsWith('/posts')) {
      listed.push(Object.fromEntries(url.searchParams));
      return route.fulfill({ json: { data: [post], meta: { current_page: 1, last_page: 1, total: 1 } } });
    }
    return route.fulfill({ status: 404, json: { message: 'Unhandled test request' } });
  });
});

test('the blog list shows tags and filters by one', async ({ page }) => {
  await page.goto('/dashboard/blog');

  await expect(page.getByRole('heading', { name: 'Blog', exact: true })).toBeVisible();
  await expect(page.getByRole('cell', { name: 'Studio' })).toBeVisible();

  await page.getByLabel('Filter by tag').selectOption('process');
  await expect.poll(() => listed.at(-1)?.tag).toBe('process');
});

test('a post is tagged in the editor and saved with its tags', async ({ page }) => {
  await page.goto('/dashboard/blog/7/edit');

  const tags = page.getByRole('list', { name: 'Tags' });
  await expect(tags.getByText('Studio')).toBeVisible();

  const field = page.getByRole('combobox', { name: 'Tags' });
  await field.fill('process');
  await field.press('Enter');
  // A tag already in use keeps its spelling.
  await expect(tags.getByText('Process', { exact: true })).toBeVisible();

  await field.fill('Kilns, Glaze,');
  await expect(tags.getByText('Kilns')).toBeVisible();
  await expect(tags.getByText('Glaze')).toBeVisible();

  await page.getByRole('button', { name: 'Remove Studio' }).click();
  await expect(tags.getByText('Studio')).toHaveCount(0);

  await page.getByRole('button', { name: 'Save draft' }).click();
  await expect.poll(() => saved?.tags).toEqual(['Process', 'Kilns', 'Glaze']);
});

test('the body editor offers code blocks', async ({ page }) => {
  await page.goto('/dashboard/blog/7/edit');

  const body = page.getByLabel('Post body');
  await body.click();
  await page.keyboard.press('End');
  await page.keyboard.press('Enter');
  await page.getByRole('button', { name: 'Code block' }).click();
  await page.keyboard.type('npm run build');

  await expect(body.locator('pre code')).toHaveText('npm run build');
  await expect(page.getByRole('button', { name: 'Code block' })).toHaveAttribute('aria-pressed', 'true');
});

test('the blog list can be shown as a gallery, and remembers it', async ({ page }) => {
  await page.goto('/dashboard/blog');

  await page.getByRole('radio', { name: 'Gallery view' }).click();
  const card = page.getByRole('listitem').filter({ hasText: 'Firing the new kiln' });
  await expect(card.getByText('/blog/firing-the-new-kiln/')).toBeVisible();
  await expect(card.getByText('Studio')).toBeVisible();
  await expect(card.getByText('Draft')).toBeVisible();
  // No cover: the title stands in for it.
  await expect(card.getByText('Firing the new kiln')).toHaveCount(2);

  await page.reload();
  await expect(page.getByRole('radio', { name: 'Gallery view' })).toHaveAttribute('aria-checked', 'true');
});

test('the editor previews the post as written, before it is saved', async ({ page }) => {
  await page.goto('/dashboard/blog/7/edit');

  await page.getByRole('textbox', { name: 'Post title' }).fill('A title not saved yet');
  await page.getByRole('radio', { name: 'Preview' }).click();

  const frame = page.frameLocator('iframe[title="Post preview"]');
  await expect(frame.getByRole('heading', { name: 'A title not saved yet' })).toBeVisible();
  expect(previewed).toMatchObject({ id: 7, title: 'A title not saved yet', tags: ['Studio'] });
  expect(saved).toBeNull();

  await page.getByRole('radio', { name: 'Phone width' }).click();
  await expect(page.getByRole('radio', { name: 'Phone width' })).toHaveAttribute('aria-checked', 'true');

  await page.getByRole('radio', { name: 'Write' }).click();
  await expect(page.getByLabel('Post body')).toBeVisible();
});

test('the tag field is a dropdown of the tags in use', async ({ page }) => {
  await page.goto('/dashboard/blog/7/edit');

  const field = page.getByRole('combobox', { name: 'Tags' });
  await field.click();
  const menu = page.getByRole('listbox', { name: 'Tags to add' });
  await expect(field).toHaveAttribute('aria-expanded', 'true');
  // Tags already on the post are not offered again; the rest carry their counts.
  await expect(menu.getByRole('option')).toHaveCount(1);
  await expect(menu.getByRole('option', { name: /Process\s*2 posts/ })).toBeVisible();

  // Typing narrows the list and offers a new tag; the keys choose.
  await field.fill('pro');
  await expect(menu.getByRole('option')).toHaveText([/Process/, /Create “pro”/]);
  await field.press('ArrowDown');
  await expect(menu.getByRole('option', { name: /Create/ })).toHaveAttribute('aria-selected', 'true');
  await field.press('ArrowUp');
  await field.press('Enter');
  await expect(page.getByRole('list', { name: 'Tags' }).getByText('Process', { exact: true })).toBeVisible();

  // A click picks too, and Escape closes the list.
  await field.fill('Kiln');
  await menu.getByRole('option', { name: 'Create “Kiln”' }).click();
  await expect(page.getByRole('list', { name: 'Tags' }).getByText('Kiln', { exact: true })).toBeVisible();
  await expect(field).toBeFocused();
  await field.press('Escape');
  await expect(field).toHaveAttribute('aria-expanded', 'false');
});

test('a new post leaves its address and excerpt to the server', async ({ page }) => {
  await page.goto('/dashboard/blog/create');

  await page.getByRole('textbox', { name: 'Post title' }).fill('A brand new post');
  await expect(page.locator('input[name="slug"]')).toHaveValue('a-brand-new-post');
  await expect(page.getByText('Made from the title. If another post has it, a number is added.')).toBeVisible();
  await expect(page.getByText('Left empty, one is written from the opening sentences when you publish.')).toBeVisible();

  await page.getByRole('button', { name: 'Save draft' }).click();
  await expect.poll(() => created).toMatchObject({ title: 'A brand new post', slug: '', excerpt: '' });
  // The address the server chose is the one shown from then on.
  await expect(page.locator('input[name="slug"]')).toHaveValue('a-brand-new-post-2');
});

test('the publish date is a day chosen from a calendar', async ({ page }) => {
  await page.clock.setFixedTime(new Date('2026-11-28T12:00:00'));
  await page.goto('/dashboard/blog/7/edit');

  const field = page.getByRole('button', { name: /^Publish date:/ });
  await expect(field).toHaveAccessibleName('Publish date: not set');
  await field.click();
  const calendar = page.getByRole('dialog', { name: 'Publish date' });
  await expect(calendar.getByText('November 2026')).toBeVisible();

  // The keys move through the month; Enter chooses.
  await page.keyboard.press('ArrowDown');
  await page.keyboard.press('Enter');
  await expect(calendar).toBeHidden();
  await expect(field).toHaveAccessibleName(/December 5, 2026|5 December 2026/);
  await expect(page.getByText('This date is in the future, so the post goes live that day.')).toBeVisible();

  // Clear takes it back to the day it is published.
  await field.click();
  await calendar.getByRole('button', { name: 'Clear', exact: true }).click();
  await expect(field).toHaveAccessibleName('Publish date: not set');

  await field.click();
  await calendar.getByRole('button', { name: 'Today', exact: true }).click();
  await page.getByRole('button', { name: 'Save draft' }).click();
  await expect.poll(() => saved?.published_at).toBe('2026-11-28');
});

test('an earlier version of a post is restored into the editor', async ({ page }) => {
  await page.goto('/dashboard/blog/7/edit');

  await page.getByRole('button', { name: 'Post history' }).click();
  const dialog = page.getByRole('dialog', { name: 'Post history' });
  await expect(dialog.getByText('An earlier title')).toBeVisible();
  await dialog.getByRole('button', { name: 'Restore' }).click();

  await expect(page.getByRole('textbox', { name: 'Post title' })).toHaveValue('An earlier title');
  await expect(page.getByLabel('Post body')).toContainText('Earlier words.');
  await expect(page.getByRole('list', { name: 'Tags' }).getByText('Process')).toBeVisible();
  // Restored, not saved: it is saved like any other change.
  expect(saved).toBeNull();
});

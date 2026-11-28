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

test.beforeEach(async ({ page }) => {
  saved = null;
  listed = [];
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
    if (path.endsWith('/posts/7') && request.method() === 'PUT') {
      saved = request.postDataJSON();
      return route.fulfill({ json: { data: { ...post, ...saved, tags: saved.tags.map((name) => ({ name, slug: name.toLowerCase() })) } } });
    }
    if (path.endsWith('/posts/7')) {
      return route.fulfill({ json: { data: post } });
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

  const field = page.getByPlaceholder('Add a tag');
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

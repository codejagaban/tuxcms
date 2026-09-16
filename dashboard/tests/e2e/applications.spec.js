import { expect, test } from '@playwright/test';

test.beforeEach(async ({ page }) => {
  await page.addInitScript(() => localStorage.setItem('auth_token', 'editor-token'));
  await page.route('**/api/v1/**', async (route) => {
    const request = route.request();
    const path = new URL(request.url()).pathname;

    if (path.endsWith('/auth/me')) {
      return route.fulfill({ json: { data: { id: 1, name: 'Editor', email: 'editor@example.com' } } });
    }
    if (path.endsWith('/site/status')) {
      return route.fulfill({ json: { data: { is_published: true } } });
    }
    if (path.endsWith('/job-applications/21/cv')) {
      return route.fulfill({ contentType: 'application/pdf', body: 'test cv' });
    }
    if (path.endsWith('/job-applications')) {
      return route.fulfill({ json: {
        data: [{
          id: 21,
          name: 'Jane Applicant',
          email: 'jane@example.com',
          phone: '07123456789',
          cover_letter: 'I have led commercial teams for five years.',
          cv_name: 'jane-cv.pdf',
          delivery_status: 'failed',
          created_at: '2026-09-16T09:30:00Z',
          job: { id: 4, title: 'Business Development Manager' },
        }],
        meta: { current_page: 1, last_page: 1, total: 1 },
      } });
    }
    return route.fulfill({ status: 404, json: { message: 'Unhandled test request' } });
  });
});

test('editor can review stored applications and download the private CV', async ({ page }) => {
  await page.goto('/dashboard/applications');

  await expect(page.getByRole('heading', { name: 'Applications' })).toBeVisible();
  await expect(page.getByText('Jane Applicant')).toBeVisible();
  await expect(page.getByText('Business Development Manager')).toBeVisible();
  await expect(page.getByText('Needs attention')).toBeVisible();

  await page.getByText('Read cover letter').click();
  await expect(page.getByText('I have led commercial teams for five years.')).toBeVisible();

  const download = page.waitForEvent('download');
  await page.getByRole('button', { name: /jane-cv\.pdf/i }).click();
  await expect((await download).suggestedFilename()).toBe('jane-cv.pdf');
});

import { expect, test } from '@playwright/test'

test('visits the catalog', async ({ page }) => {
  await page.goto('/')
  await expect(page.getByRole('heading', { name: 'Catalogue' })).toBeVisible()
})

import { screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { createFakeCatalogRepository } from '@/modules/catalog/testing/fakeCatalogRepository'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'

describe('App', () => {
  it('renders the shop layout and catalog', async () => {
    await renderApp({ repository: createFakeCatalogRepository([nuvoraTee]) })

    await waitFor(() => {
      expect(screen.getByRole('link', { name: 'Shoppy' })).toBeTruthy()
      expect(screen.getByRole('heading', { name: 'Catalogue' })).toBeTruthy()
    })
  })
})

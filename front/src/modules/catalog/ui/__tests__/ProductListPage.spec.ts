import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import {
  createFailingCatalogRepository,
  createFakeCatalogRepository,
} from '../../testing/fakeCatalogRepository'
import { createProduct, nuvoraTee, outOfStockMug } from '../../testing/productFixtures'

describe('ProductListPage', () => {
  it('shows catalog products', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee, outOfStockMug]),
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Notre Collection' })).toBeTruthy()
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
      expect(screen.getByRole('heading', { name: 'Nuvora Mug' })).toBeTruthy()
    })
  })

  it('shows an empty state', async () => {
    await renderApp({ repository: createFakeCatalogRepository([]) })

    await waitFor(() => {
      expect(screen.getByText('Aucun produit')).toBeTruthy()
    })
  })

  it('shows an API error', async () => {
    await renderApp({
      repository: createFailingCatalogRepository(new ApiError(500, 'internal_error', 'Boom.')),
    })

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Boom.')
    })
  })

  it('paginates when there are more products than the page size', async () => {
    const products = Array.from({ length: 21 }, (_, index) =>
      createProduct({
        id: `00000000-0000-4000-8000-${String(index + 1).padStart(12, '0')}`,
        slug: `produit-${index + 1}`,
        name: `Produit ${index + 1}`,
        createdAt: new Date(Date.UTC(2026, 7, 20, 12, 0, 0) - index * 1000).toISOString(),
      }),
    )

    await renderApp({ repository: createFakeCatalogRepository(products) })

    await waitFor(() => {
      expect(screen.getByText('Produit 1')).toBeTruthy()
      expect(screen.queryByText('Produit 21')).toBeNull()
    })

    await userEvent.click(screen.getByRole('link', { name: 'Page suivante' }))

    await waitFor(() => {
      expect(screen.getByText('Produit 21')).toBeTruthy()
      expect(screen.queryByText('Produit 1')).toBeNull()
    })
  })

  it('loads the page from the query string', async () => {
    const products = Array.from({ length: 21 }, (_, index) =>
      createProduct({
        id: `00000000-0000-4000-8000-${String(index + 1).padStart(12, '0')}`,
        slug: `produit-${index + 1}`,
        name: `Produit ${index + 1}`,
        createdAt: new Date(Date.UTC(2026, 7, 20, 12, 0, 0) - index * 1000).toISOString(),
      }),
    )

    await renderApp({
      repository: createFakeCatalogRepository(products),
      path: '/?page=2',
    })

    await waitFor(() => {
      expect(screen.getByText('Produit 21')).toBeTruthy()
    })
  })

  it('searches products by name', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee, outOfStockMug]),
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Mug' })).toBeTruthy()
    })

    await userEvent.type(screen.getByPlaceholderText('Rechercher un produit...'), 'tee')
    await userEvent.keyboard('{Enter}')

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
      expect(screen.queryByRole('heading', { name: 'Nuvora Mug' })).toBeNull()
    })
  })

  it('sorts products by price', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee, outOfStockMug]),
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
    })

    await userEvent.selectOptions(screen.getByLabelText('Trier les produits'), 'price_asc')

    await waitFor(() => {
      const names = screen.getAllByRole('heading').map((heading) => heading.textContent)
      expect(names.indexOf('Nuvora Mug')).toBeLessThan(names.indexOf('Nuvora Tee'))
    })
  })

  it('shows an empty search state', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      path: '/?q=hoodie',
    })

    await waitFor(() => {
      expect(screen.getByText('Aucun résultat')).toBeTruthy()
      expect(screen.getByText('Aucun produit ne correspond à « hoodie ».')).toBeTruthy()
    })
  })
})

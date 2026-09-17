import { screen, waitFor } from '@testing-library/vue'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeCartRepository } from '@/modules/cart/testing/FakeCartRepository'
import { createFakeCatalogRepository } from '../../testing/fakeCatalogRepository'
import { nuvoraTee, nuvoraTeeGallery, outOfStockMug } from '../../testing/productFixtures'

describe('ProductDetailPage', () => {
  it('shows product details', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      path: `/products/${nuvoraTee.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
      expect(screen.getByRole('img', { name: 'Nuvora Tee' })).toBeTruthy()
      expect(screen.getByText('Soft cotton tee')).toBeTruthy()
      expect(screen.getByText(/19,99/)).toBeTruthy()
      expect(screen.getByText('En stock (10)')).toBeTruthy()
    })
    expect(screen.getByRole('link', { name: 'Retour au catalogue' }).getAttribute('href')).toBe('/')
  })

  it('shows a thumbnail list to browse the gallery', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTeeGallery]),
      path: `/products/${nuvoraTeeGallery.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('img', { name: 'Nuvora Tee (1/3)' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: "Voir l'image 2" }))

    expect(screen.getByRole('img', { name: 'Nuvora Tee (2/3)' }).getAttribute('src')).toBe(
      nuvoraTeeGallery.imageUrls[1],
    )
  })

  it('shows an out of stock product', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([outOfStockMug]),
      path: `/products/${outOfStockMug.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Mug' })).toBeTruthy()
      expect(screen.getByText('Rupture de stock')).toBeTruthy()
    })
  })

  it('shows a dedicated message when the product is missing', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      path: '/products/missing',
    })

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Ce produit est introuvable.')
    })
  })

  it('adds the product to the cart when logged in', async () => {
    const cartRepository = new FakeCartRepository()
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      cartRepository,
      session: visitorSession,
      path: `/products/${nuvoraTee.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Ajouter au panier' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Ajouter au panier' }))

    await waitFor(() => {
      expect(cartRepository.added).toEqual([{ productId: nuvoraTee.id, quantity: 1 }])
      expect(screen.getByRole('status').textContent).toContain('Ajouté au panier.')
      expect(screen.getByRole('link', { name: 'Panier (1)' })).toBeTruthy()
    })
  })

  it('redirects to login when the visitor session is no longer valid', async () => {
    const cartRepository = new FakeCartRepository()
    cartRepository.addError = new ApiError(401, 'unauthenticated', 'The request is not authenticated.')
    const { router, authSession } = await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      cartRepository,
      session: visitorSession,
      path: `/products/${nuvoraTee.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Ajouter au panier' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Ajouter au panier' }))

    await waitFor(() => {
      expect(authSession.isAuthenticated.value).toBe(false)
      expect(router.currentRoute.value.fullPath).toBe(`/login?redirect=/products/${nuvoraTee.slug}`)
    })
  })

  it('redirects a guest to login before adding to cart', async () => {
    const { router } = await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      path: `/products/${nuvoraTee.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Ajouter au panier' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Ajouter au panier' }))

    await waitFor(() => {
      expect(router.currentRoute.value.fullPath).toBe(`/login?redirect=/products/${nuvoraTee.slug}`)
    })
  })

  it('canonicalizes a uuid url to the product slug', async () => {
    const { router } = await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      path: `/products/${nuvoraTee.id}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
      expect(router.currentRoute.value.fullPath).toBe(`/products/${nuvoraTee.slug}`)
    })
  })

  it('shows a stock error when adding to cart', async () => {
    const cartRepository = new FakeCartRepository()
    cartRepository.addError = new ApiError(409, 'insufficient_product_stock', 'Not enough stock.')
    await renderApp({
      repository: createFakeCatalogRepository([nuvoraTee]),
      cartRepository,
      session: visitorSession,
      path: `/products/${nuvoraTee.slug}`,
    })

    await waitFor(() => {
      expect(screen.getByRole('button', { name: 'Ajouter au panier' })).toBeTruthy()
    })

    await userEvent.click(screen.getByRole('button', { name: 'Ajouter au panier' }))

    await waitFor(() => {
      expect(screen.getByRole('alert').textContent).toContain('Stock insuffisant pour ce produit.')
    })
  })

  it('disables add to cart when the product is out of stock', async () => {
    await renderApp({
      repository: createFakeCatalogRepository([outOfStockMug]),
      session: visitorSession,
      path: `/products/${outOfStockMug.slug}`,
    })

    await waitFor(() => {
      expect(
        (screen.getByRole('button', { name: 'Ajouter au panier' }) as HTMLButtonElement).disabled,
      ).toBe(true)
    })
  })
})

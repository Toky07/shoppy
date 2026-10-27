import { screen, waitFor } from '@testing-library/vue'
import { describe, expect, it } from 'vitest'
import { renderApp } from '@/shared/testing/renderApp'
import { visitorSession } from '@/modules/auth/testing/authFixtures'
import { FakeFavoriteRepository } from '../../../testing/FakeFavoriteRepository'
import { createFakeCatalogRepository } from '../../../testing/fakeCatalogRepository'
import { nuvoraTee } from '../../../testing/productFixtures'

describe('FavoritesPage', () => {
  it('loads account favorites for a signed-in customer', async () => {
    await renderApp({
      path: '/favorites',
      session: visitorSession,
      favoriteRepository: new FakeFavoriteRepository([nuvoraTee.id]),
      repository: createFakeCatalogRepository([nuvoraTee]),
    })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Nuvora Tee' })).toBeTruthy()
    })
    expect(screen.getByText(/Enregistrée sur votre compte/)).toBeTruthy()
  })

  it('shows an empty wishlist for a guest', async () => {
    await renderApp({ path: '/favorites' })

    await waitFor(() => {
      expect(screen.getByRole('heading', { name: 'Votre liste est vide' })).toBeTruthy()
    })
  })
})

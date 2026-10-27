import { ref } from 'vue'
import { describe, expect, it } from 'vitest'
import { waitFor } from '@testing-library/vue'
import { createFavoritesState } from '../createFavoritesState'
import { createMemoryGuestFavorites } from '../../data/guestFavoriteStorage'
import { FakeFavoriteRepository } from '../../testing/FakeFavoriteRepository'
import { nuvoraTee, outOfStockMug } from '../../testing/productFixtures'

describe('createFavoritesState', () => {
  it('keeps favorites on the device while logged out', async () => {
    const repository = new FakeFavoriteRepository([nuvoraTee.id])
    const guest = createMemoryGuestFavorites()
    guest.write([outOfStockMug.id])
    const state = createFavoritesState(repository, ref(false), guest)

    await waitFor(() => {
      expect(state.favorites.value).toEqual([outOfStockMug.id])
    })
    expect(repository.listCount).toBe(0)
    expect(state.count.value).toBe(1)
  })

  it('loads account favorites when authenticated', async () => {
    const repository = new FakeFavoriteRepository([nuvoraTee.id])
    const state = createFavoritesState(repository, ref(true))

    await waitFor(() => {
      expect(state.favorites.value).toEqual([nuvoraTee.id])
    })
    expect(state.isFavorite(nuvoraTee.id)).toBe(true)
  })

  it('merges guest favorites when the customer logs in', async () => {
    const repository = new FakeFavoriteRepository([nuvoraTee.id])
    const guest = createMemoryGuestFavorites()
    guest.write([outOfStockMug.id])
    const isAuthenticated = ref(false)
    const state = createFavoritesState(repository, isAuthenticated, guest)

    await waitFor(() => {
      expect(state.favorites.value).toEqual([outOfStockMug.id])
    })

    isAuthenticated.value = true

    await waitFor(() => {
      expect(repository.merged).toEqual([outOfStockMug.id])
      expect(guest.read()).toEqual([])
      expect(state.favorites.value).toEqual([outOfStockMug.id, nuvoraTee.id])
    })
  })

  it('toggles on the device or through the API', async () => {
    const repository = new FakeFavoriteRepository()
    const guest = createMemoryGuestFavorites()
    const isAuthenticated = ref(false)
    const state = createFavoritesState(repository, isAuthenticated, guest)

    await state.toggle(nuvoraTee.id)
    expect(guest.read()).toEqual([nuvoraTee.id])
    expect(state.isFavorite(nuvoraTee.id)).toBe(true)

    isAuthenticated.value = true
    await waitFor(() => {
      expect(repository.merged).toEqual([nuvoraTee.id])
    })

    await state.toggle(outOfStockMug.id)
    expect(repository.added).toEqual([outOfStockMug.id])
    expect(state.favorites.value).toEqual([outOfStockMug.id, nuvoraTee.id])

    await state.toggle(nuvoraTee.id)
    expect(repository.removed).toEqual([nuvoraTee.id])
    expect(state.isFavorite(nuvoraTee.id)).toBe(false)
  })
})

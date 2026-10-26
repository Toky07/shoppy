import { inject } from 'vue'
import { createMemoryGuestFavorites } from '../data/guestFavoriteStorage'
import { createFavoritesState } from './createFavoritesState'
import type { FavoriteRepository } from './FavoriteRepository'
import { favoritesStateKey } from './favoritesStateKey'

const unusedAccount: FavoriteRepository = {
  list: async () => [],
  add: async () => [],
  remove: async () => [],
  clear: async () => [],
  merge: async (productIds) => productIds,
}

const fallback = createFavoritesState(unusedAccount, false, createMemoryGuestFavorites())

export function useFavorites() {
  return inject(favoritesStateKey, fallback)
}

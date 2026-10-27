import { computed, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import {
  createMemoryGuestFavorites,
  type GuestFavoriteStore,
} from '../data/guestFavoriteStorage'
import type { FavoriteRepository } from './FavoriteRepository'

export function createFavoritesState(
  repository: FavoriteRepository,
  isAuthenticated: MaybeRefOrGetter<boolean>,
  guestFavorites: GuestFavoriteStore = createMemoryGuestFavorites(),
) {
  const ids = ref<string[]>([])
  const loading = ref(false)

  function showGuest() {
    ids.value = guestFavorites.read()
    loading.value = false
  }

  async function refresh() {
    if (!toValue(isAuthenticated)) {
      showGuest()
      return
    }

    loading.value = true

    try {
      const local = guestFavorites.read()
      if (local.length > 0) {
        ids.value = await repository.merge(local)
        guestFavorites.clear()
        return
      }

      ids.value = await repository.list()
    } catch {
      ids.value = []
    } finally {
      loading.value = false
    }
  }

  watch(() => toValue(isAuthenticated), refresh, { immediate: true })

  function changeGuest(update: (current: string[]) => string[]) {
    const next = update(guestFavorites.read())
    guestFavorites.write(next)
    ids.value = next
  }

  return {
    favorites: computed(() => ids.value),
    count: computed(() => ids.value.length),
    loading: computed(() => loading.value),
    isFavorite: (productId: string) => ids.value.includes(productId),
    refresh,
    toggle: (productId: string) => {
      if (!toValue(isAuthenticated)) {
        changeGuest((current) =>
          current.includes(productId)
            ? current.filter((id) => id !== productId)
            : [productId, ...current],
        )
        return Promise.resolve()
      }

      return (ids.value.includes(productId) ? repository.remove(productId) : repository.add(productId)).then(
        (next) => {
          ids.value = next
        },
      )
    },
    remove: (productId: string) => {
      if (!toValue(isAuthenticated)) {
        changeGuest((current) => current.filter((id) => id !== productId))
        return Promise.resolve()
      }

      return repository.remove(productId).then((next) => {
        ids.value = next
      })
    },
    clear: () => {
      if (!toValue(isAuthenticated)) {
        guestFavorites.clear()
        ids.value = []
        return Promise.resolve()
      }

      return repository.clear().then((next) => {
        ids.value = next
      })
    },
  }
}

export type FavoritesState = ReturnType<typeof createFavoritesState>

export type GuestFavoriteStore = {
  read(): string[]
  write(productIds: string[]): void
  clear(): void
}

const storageKey = 'shoppy.favorites'

export function createMemoryGuestFavorites(productIds: string[] = []): GuestFavoriteStore {
  let ids = [...productIds]

  return {
    read: () => [...ids],
    write: (next) => {
      ids = [...next]
    },
    clear: () => {
      ids = []
    },
  }
}

export function createLocalGuestFavorites(storage: Storage): GuestFavoriteStore {
  return {
    read: () => {
      try {
        const raw = storage.getItem(storageKey)
        const parsed: unknown = raw === null ? [] : JSON.parse(raw)

        return Array.isArray(parsed) ? parsed.filter((id): id is string => typeof id === 'string') : []
      } catch {
        return []
      }
    },
    write: (productIds) => {
      storage.setItem(storageKey, JSON.stringify(productIds))
    },
    clear: () => {
      storage.removeItem(storageKey)
    },
  }
}

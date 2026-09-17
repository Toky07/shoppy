import { computed, ref } from 'vue'

const STORAGE_KEY = 'shoppy.favorites'

function readStored(): string[] {
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY)
    const parsed: unknown = raw === null ? [] : JSON.parse(raw)

    return Array.isArray(parsed) ? parsed.filter((id): id is string => typeof id === 'string') : []
  } catch {
    return []
  }
}

const ids = ref<string[]>(readStored())

function persist() {
  try {
    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(ids.value))
  } catch {
    /* stockage indisponible */
  }
}

/** Liste d'envies, gardée en local : aucun compte requis. */
export function useFavorites() {
  const favorites = computed(() => ids.value)
  const count = computed(() => ids.value.length)

  function isFavorite(productId: string) {
    return ids.value.includes(productId)
  }

  function toggle(productId: string) {
    ids.value = isFavorite(productId)
      ? ids.value.filter((id) => id !== productId)
      : [productId, ...ids.value]
    persist()
  }

  function remove(productId: string) {
    ids.value = ids.value.filter((id) => id !== productId)
    persist()
  }

  function clear() {
    ids.value = []
    persist()
  }

  return { favorites, count, isFavorite, toggle, remove, clear }
}

import { ref } from 'vue'

export const CATALOG_VIEWS = ['grid', 'list'] as const

export type CatalogView = (typeof CATALOG_VIEWS)[number]

const STORAGE_KEY = 'shoppy.catalogView'

function readStored(): CatalogView {
  try {
    const stored = window.localStorage.getItem(STORAGE_KEY)
    return (CATALOG_VIEWS as readonly string[]).includes(stored ?? '')
      ? (stored as CatalogView)
      : 'grid'
  } catch {
    return 'grid'
  }
}

const view = ref<CatalogView>(readStored())

/** Grille ou liste, mémorisé entre les visites. */
export function useCatalogView() {
  function setView(next: CatalogView) {
    view.value = next

    try {
      window.localStorage.setItem(STORAGE_KEY, next)
    } catch {
      /* stockage indisponible */
    }
  }

  return { view, setView }
}

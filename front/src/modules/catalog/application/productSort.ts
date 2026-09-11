export const PRODUCT_SORTS = ['newest', 'oldest', 'price_asc', 'price_desc', 'name_asc'] as const

export type ProductSort = (typeof PRODUCT_SORTS)[number]

export const DEFAULT_PRODUCT_SORT: ProductSort = 'newest'

export const PRODUCT_SORT_OPTIONS: { value: ProductSort; label: string }[] = [
  { value: 'newest', label: 'Nouveautés' },
  { value: 'oldest', label: 'Plus anciens' },
  { value: 'price_asc', label: 'Prix croissant' },
  { value: 'price_desc', label: 'Prix décroissant' },
  { value: 'name_asc', label: 'Nom A-Z' },
]

export function isProductSort(value: string): value is ProductSort {
  return (PRODUCT_SORTS as readonly string[]).includes(value)
}

export function parseProductSort(value: unknown): ProductSort {
  const raw = Array.isArray(value) ? value[0] : value

  return typeof raw === 'string' && isProductSort(raw) ? raw : DEFAULT_PRODUCT_SORT
}

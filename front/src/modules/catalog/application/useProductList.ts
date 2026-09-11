import { type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { ProductPage } from '../domain/ProductPage'
import type { CatalogRepository, ListProductsQuery } from './CatalogRepository'
import { listProducts } from './listProducts'

export type ProductListStatus = 'loading' | 'ready' | 'empty' | 'error'

export function useProductList(
  repository: CatalogRepository,
  query: MaybeRefOrGetter<ListProductsQuery>,
) {
  const status = ref<ProductListStatus>('loading')
  const page = ref<ProductPage | null>(null)
  const error = ref<ApiError | null>(null)

  async function load() {
    status.value = 'loading'
    error.value = null

    try {
      const result = await listProducts(repository, toValue(query))
      page.value = result
      status.value = result.items.length === 0 ? 'empty' : 'ready'
    } catch (caught) {
      page.value = null
      error.value = toApiError(caught)
      status.value = 'error'
    }
  }

  watch(() => toValue(query), load, { immediate: true, deep: true })

  return { status, page, error, reload: load }
}

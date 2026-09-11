import { type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { Product } from '../domain/Product'
import type { CatalogRepository } from './CatalogRepository'
import { getProduct } from './getProduct'

export type ProductStatus = 'loading' | 'ready' | 'error'

export function useProduct(repository: CatalogRepository, productId: MaybeRefOrGetter<string>) {
  const status = ref<ProductStatus>('loading')
  const product = ref<Product | null>(null)
  const error = ref<ApiError | null>(null)

  async function load() {
    status.value = 'loading'
    error.value = null
    product.value = null

    try {
      product.value = await getProduct(repository, toValue(productId))
      status.value = 'ready'
    } catch (caught) {
      error.value = toApiError(caught)
      status.value = 'error'
    }
  }

  watch(() => toValue(productId), load, { immediate: true })

  return { status, product, error }
}

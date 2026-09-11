import { type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { OrderPage } from '../domain/OrderPage'
import type { ListOrdersQuery, OrderRepository } from './OrderRepository'

export type OrderListStatus = 'loading' | 'ready' | 'empty' | 'error'

export function useOrderList(
  repository: OrderRepository,
  query: MaybeRefOrGetter<ListOrdersQuery>,
  enabled: MaybeRefOrGetter<boolean> = true,
  list: (repository: OrderRepository, query: ListOrdersQuery) => Promise<OrderPage> = (current, currentQuery) =>
    current.list(currentQuery),
) {
  const status = ref<OrderListStatus>('loading')
  const page = ref<OrderPage | null>(null)
  const error = ref<ApiError | null>(null)

  async function load() {
    if (!toValue(enabled)) {
      status.value = 'empty'
      page.value = null
      error.value = null
      return
    }

    status.value = 'loading'
    error.value = null

    try {
      const result = await list(repository, toValue(query))
      page.value = result
      status.value = result.items.length === 0 ? 'empty' : 'ready'
    } catch (caught) {
      page.value = null
      error.value = toApiError(caught)
      status.value = 'error'
    }
  }

  watch([() => toValue(query), () => toValue(enabled)], load, { immediate: true, deep: true })

  return { status, page, error }
}

import { type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { Order } from '../domain/Order'
import type { OrderRepository } from './OrderRepository'

export type OrderDetailStatus = 'loading' | 'ready' | 'error'

export function useOrder(
  repository: OrderRepository,
  orderId: MaybeRefOrGetter<string>,
  enabled: MaybeRefOrGetter<boolean> = true,
) {
  const status = ref<OrderDetailStatus>('loading')
  const order = ref<Order | null>(null)
  const error = ref<ApiError | null>(null)

  async function load() {
    if (!toValue(enabled)) {
      status.value = 'loading'
      order.value = null
      error.value = null
      return
    }

    status.value = 'loading'
    error.value = null
    order.value = null

    try {
      order.value = await repository.getById(toValue(orderId))
      status.value = 'ready'
    } catch (caught) {
      error.value = toApiError(caught)
      status.value = 'error'
    }
  }

  watch([() => toValue(orderId), () => toValue(enabled)], load, { immediate: true })

  return { status, order, error, reload: load }
}

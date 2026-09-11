import { type MaybeRefOrGetter, ref, toValue, watch } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { Payment } from '../domain/Payment'
import type { PaymentRepository } from './PaymentRepository'

export function usePaymentByOrder(
  repository: PaymentRepository,
  orderId: MaybeRefOrGetter<string>,
  enabled: MaybeRefOrGetter<boolean> = true,
) {
  const payment = ref<Payment | null>(null)
  const error = ref<ApiError | null>(null)
  const loading = ref(false)

  async function load() {
    if (!toValue(enabled)) {
      payment.value = null
      error.value = null
      loading.value = false
      return
    }

    loading.value = true
    error.value = null
    payment.value = null

    try {
      payment.value = await repository.getByOrder(toValue(orderId))
    } catch (caught) {
      error.value = toApiError(caught)
    } finally {
      loading.value = false
    }
  }

  watch([() => toValue(orderId), () => toValue(enabled)], load, { immediate: true })

  return { payment, error, loading, reload: load }
}

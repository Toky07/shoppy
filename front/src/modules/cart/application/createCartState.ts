import { computed, readonly, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { Cart } from '../domain/Cart'
import type { CheckoutAddresses } from '../domain/CheckoutAddresses'
import type { CheckoutResult } from '../domain/CheckoutResult'
import type { CartRepository } from './CartRepository'

export function createCartState(
  repository: CartRepository,
  isAuthenticated: MaybeRefOrGetter<boolean>,
) {
  const cart = ref<Cart | null>(null)
  const loading = ref(false)
  const error = ref<ApiError | null>(null)

  async function refresh() {
    if (!toValue(isAuthenticated)) {
      cart.value = null
      error.value = null
      loading.value = false
      return
    }

    loading.value = true
    error.value = null

    try {
      cart.value = await repository.get()
    } catch (caught) {
      error.value = toApiError(caught)
      cart.value = null
    } finally {
      loading.value = false
    }
  }

  watch(() => toValue(isAuthenticated), refresh, { immediate: true })

  async function mutate(run: () => Promise<Cart>): Promise<Cart> {
    const next = await run()
    cart.value = next
    return next
  }

  async function checkout(addresses: CheckoutAddresses): Promise<CheckoutResult> {
    const result = await repository.checkout(addresses)
    cart.value = await repository.get()
    return result
  }

  return {
    cart: readonly(cart),
    loading: readonly(loading),
    error: readonly(error),
    itemCount: computed(() => cart.value?.items.reduce((sum, item) => sum + item.quantity, 0) ?? 0),
    refresh,
    addItem: (productId: string, quantity: number, variantId?: string | null) =>
      mutate(() => repository.addItem(productId, quantity, variantId)),
    updateItem: (productId: string, quantity: number, variantId?: string | null) =>
      mutate(() => repository.updateItem(productId, quantity, variantId)),
    removeItem: (productId: string, variantId?: string | null) =>
      mutate(() => repository.removeItem(productId, variantId)),
    clear: () => mutate(() => repository.clear()),
    checkout,
  }
}

export type CartState = ReturnType<typeof createCartState>

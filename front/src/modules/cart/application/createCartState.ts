import { computed, readonly, ref, toValue, watch, type MaybeRefOrGetter } from 'vue'
import { ApiError } from '@/shared/http/ApiError'
import { toApiError } from '@/shared/http/toApiError'
import type { Cart } from '../domain/Cart'
import type { CheckoutAddresses } from '../domain/CheckoutAddresses'
import type { CheckoutResult } from '../domain/CheckoutResult'
import {
  createMemoryGuestCart,
  guestCartFromLines,
  type GuestCartLine,
  type GuestCartStore,
} from '../data/guestCartStorage'
import type { CartRepository } from './CartRepository'

export type GuestCartSnapshot = {
  name: string
  unitPriceCents: number
  availableStock: number
}

export function createCartState(
  repository: CartRepository,
  isAuthenticated: MaybeRefOrGetter<boolean>,
  guestCart: GuestCartStore = createMemoryGuestCart(),
) {
  const cart = ref<Cart | null>(null)
  const loading = ref(false)
  const error = ref<ApiError | null>(null)

  function showGuestCart() {
    cart.value = guestCartFromLines(guestCart.read())
    error.value = null
    loading.value = false
  }

  async function refresh() {
    if (!toValue(isAuthenticated)) {
      showGuestCart()
      return
    }

    loading.value = true
    error.value = null

    try {
      const guestLines = guestCart.read()
      if (guestLines.length > 0) {
        cart.value = await repository.merge(
          guestLines.map((line) => ({
            productId: line.productId,
            quantity: line.quantity,
            variantId: line.variantId,
          })),
        )
        guestCart.clear()
        return
      }

      cart.value = await repository.get()
    } catch (caught) {
      error.value = toApiError(caught)
      cart.value = null
    } finally {
      loading.value = false
    }
  }

  watch(() => toValue(isAuthenticated), refresh, { immediate: true })

  function changeGuest(update: (lines: GuestCartLine[]) => GuestCartLine[]) {
    const next = update(guestCart.read())
    guestCart.write(next)
    showGuestCart()
  }

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
    addItem: (
      productId: string,
      quantity: number,
      variantId?: string | null,
      snapshot?: GuestCartSnapshot,
    ) => {
      if (!toValue(isAuthenticated)) {
        changeGuest((lines) => addGuestLine(lines, productId, quantity, variantId ?? null, snapshot))
        return Promise.resolve(cart.value as Cart)
      }

      return mutate(() => repository.addItem(productId, quantity, variantId))
    },
    updateItem: (productId: string, quantity: number, variantId?: string | null) => {
      if (!toValue(isAuthenticated)) {
        changeGuest((lines) =>
          lines.map((line) =>
            line.productId === productId && line.variantId === (variantId ?? null)
              ? { ...line, quantity }
              : line,
          ),
        )
        return Promise.resolve(cart.value as Cart)
      }

      return mutate(() => repository.updateItem(productId, quantity, variantId))
    },
    removeItem: (productId: string, variantId?: string | null) => {
      if (!toValue(isAuthenticated)) {
        changeGuest((lines) =>
          lines.filter((line) => !(line.productId === productId && line.variantId === (variantId ?? null))),
        )
        return Promise.resolve(cart.value as Cart)
      }

      return mutate(() => repository.removeItem(productId, variantId))
    },
    clear: () => {
      if (!toValue(isAuthenticated)) {
        guestCart.clear()
        showGuestCart()
        return Promise.resolve(cart.value as Cart)
      }

      return mutate(() => repository.clear())
    },
    checkout,
  }
}

function addGuestLine(
  lines: GuestCartLine[],
  productId: string,
  quantity: number,
  variantId: string | null,
  snapshot?: GuestCartSnapshot,
): GuestCartLine[] {
  const existing = lines.find((line) => line.productId === productId && line.variantId === variantId)
  if (existing) {
    return lines.map((line) =>
      line === existing ? { ...line, quantity: line.quantity + quantity } : line,
    )
  }

  return [
    ...lines,
    {
      productId,
      variantId,
      quantity,
      name: snapshot?.name ?? 'Article',
      unitPriceCents: snapshot?.unitPriceCents ?? 0,
      availableStock: snapshot?.availableStock ?? quantity,
    },
  ]
}

export type CartState = ReturnType<typeof createCartState>

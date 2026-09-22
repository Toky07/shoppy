import type { Cart } from '../domain/Cart'
import type { CartItem } from '../domain/CartItem'
import type { CheckoutResult } from '../domain/CheckoutResult'
import { visitorUser } from '@/modules/auth/testing/authFixtures'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'

export const cartTeeItem: CartItem = {
  productId: nuvoraTee.id,
  name: nuvoraTee.name,
  quantity: 2,
  unitPrice: { cents: 1999, currency: 'EUR' },
  lineTotal: { cents: 3998, currency: 'EUR' },
  availableStock: 10,
  variantId: null,
}

export function emptyCart(customerId = visitorUser.id): Cart {
  return {
    id: null,
    customerId,
    items: [],
    total: { cents: 0, currency: 'EUR' },
    updatedAt: null,
  }
}

export const filledCart: Cart = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
  customerId: visitorUser.id,
  items: [cartTeeItem],
  total: { cents: 3998, currency: 'EUR' },
  updatedAt: '2026-09-10T12:00:00+00:00',
}

export const pendingCheckout: CheckoutResult = {
  id: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
  status: 'pending',
}

export function createCartItemJson(overrides: Record<string, unknown> = {}) {
  return {
    productId: cartTeeItem.productId,
    name: cartTeeItem.name,
    quantity: cartTeeItem.quantity,
    unitPrice: { cents: cartTeeItem.unitPrice.cents, currency: cartTeeItem.unitPrice.currency },
    lineTotal: { cents: cartTeeItem.lineTotal.cents, currency: cartTeeItem.lineTotal.currency },
    availableStock: cartTeeItem.availableStock,
    ...overrides,
  }
}

export function createCartJson(overrides: Record<string, unknown> = {}) {
  return {
    id: filledCart.id,
    customerId: filledCart.customerId,
    items: [createCartItemJson()],
    total: { cents: filledCart.total.cents, currency: filledCart.total.currency },
    updatedAt: filledCart.updatedAt,
    ...overrides,
  }
}

export function createCheckoutJson(overrides: Record<string, unknown> = {}) {
  return {
    id: pendingCheckout.id,
    customerId: visitorUser.id,
    status: pendingCheckout.status,
    items: [],
    total: { cents: 3998, currency: 'EUR' },
    createdAt: '2026-09-10T12:05:00+00:00',
    ...overrides,
  }
}

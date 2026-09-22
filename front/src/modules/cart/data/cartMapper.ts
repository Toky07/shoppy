import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapMoney } from '@/shared/money/mapMoney'
import { isRecord } from '@/shared/types/isRecord'
import type { Cart } from '../domain/Cart'
import type { CartItem } from '../domain/CartItem'
import type { CheckoutResult } from '../domain/CheckoutResult'

export function mapCartItem(payload: unknown): CartItem {
  if (
    !isRecord(payload) ||
    typeof payload.productId !== 'string' ||
    typeof payload.name !== 'string' ||
    typeof payload.quantity !== 'number' ||
    typeof payload.availableStock !== 'number'
  ) {
    throw new InvalidResponseError('Invalid cart item payload.')
  }

  return {
    productId: payload.productId,
    name: payload.name,
    quantity: payload.quantity,
    unitPrice: mapMoney(payload.unitPrice, 'Invalid cart item price.'),
    lineTotal: mapMoney(payload.lineTotal, 'Invalid cart item total.'),
    availableStock: payload.availableStock,
    variantId: typeof payload.variantId === 'string' ? payload.variantId : null,
  }
}

export function mapCart(payload: unknown): Cart {
  if (
    !isRecord(payload) ||
    (payload.id !== null && typeof payload.id !== 'string') ||
    typeof payload.customerId !== 'string' ||
    !Array.isArray(payload.items) ||
    (payload.updatedAt !== null && typeof payload.updatedAt !== 'string')
  ) {
    throw new InvalidResponseError('Invalid cart payload.')
  }

  return {
    id: payload.id,
    customerId: payload.customerId,
    items: payload.items.map(mapCartItem),
    total: mapMoney(payload.total, 'Invalid cart total.'),
    updatedAt: payload.updatedAt,
  }
}

export function mapCheckoutResult(payload: unknown): CheckoutResult {
  if (!isRecord(payload) || typeof payload.id !== 'string' || typeof payload.status !== 'string') {
    throw new InvalidResponseError('Invalid order payload.')
  }

  return {
    id: payload.id,
    status: payload.status,
  }
}

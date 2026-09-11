import type { Order } from '../domain/Order'
import type { OrderItem } from '../domain/OrderItem'
import { visitorUser } from '@/modules/auth/testing/authFixtures'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'
import { pendingCheckout } from '@/modules/cart/testing/cartFixtures'

export const orderTeeItem: OrderItem = {
  productId: nuvoraTee.id,
  name: nuvoraTee.name,
  quantity: 2,
  unitPrice: { cents: 1999, currency: 'EUR' },
  lineTotal: { cents: 3998, currency: 'EUR' },
}

export const pendingOrder: Order = {
  id: pendingCheckout.id,
  customerId: visitorUser.id,
  status: 'pending',
  items: [orderTeeItem],
  total: { cents: 3998, currency: 'EUR' },
  createdAt: '2026-09-10T12:05:00+00:00',
}

export const paidOrder: Order = {
  ...pendingOrder,
  status: 'paid',
}

export const cancelledOrder: Order = {
  ...pendingOrder,
  id: 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
  status: 'cancelled',
  createdAt: '2026-09-09T08:00:00+00:00',
}

export function createOrderItemJson(overrides: Record<string, unknown> = {}) {
  return {
    productId: orderTeeItem.productId,
    name: orderTeeItem.name,
    quantity: orderTeeItem.quantity,
    unitPrice: { cents: orderTeeItem.unitPrice.cents, currency: orderTeeItem.unitPrice.currency },
    lineTotal: { cents: orderTeeItem.lineTotal.cents, currency: orderTeeItem.lineTotal.currency },
    ...overrides,
  }
}

export function createOrderJson(overrides: Record<string, unknown> = {}) {
  return {
    id: pendingOrder.id,
    customerId: pendingOrder.customerId,
    status: pendingOrder.status,
    items: [createOrderItemJson()],
    total: { cents: pendingOrder.total.cents, currency: pendingOrder.total.currency },
    createdAt: pendingOrder.createdAt,
    ...overrides,
  }
}

import type { Order } from '../domain/Order'
import type { OrderItem } from '../domain/OrderItem'
import type { PostalAddress } from '../domain/PostalAddress'
import { quoteShipping } from '../domain/ShippingMethod'
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

export const parisAddress: PostalAddress = {
  recipient: 'Ada Lovelace',
  line1: '10 rue de la Paix',
  line2: null,
  postalCode: '75002',
  city: 'Paris',
  country: 'FR',
}

export const pendingOrder: Order = {
  id: pendingCheckout.id,
  customerId: visitorUser.id,
  customerEmail: 'test@test.test',
  status: 'pending',
  items: [orderTeeItem],
  total: { cents: 4488, currency: 'EUR' },
  createdAt: '2026-09-10T12:05:00+00:00',
  shippingAddress: parisAddress,
  billingAddress: parisAddress,
  shipping: quoteShipping('standard', 3998),
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
    shippingAddress: parisAddress,
    billingAddress: parisAddress,
    shipping: pendingOrder.shipping,
    ...overrides,
  }
}

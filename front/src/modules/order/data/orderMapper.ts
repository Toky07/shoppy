import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapMoney } from '@/shared/money/mapMoney'
import { isRecord } from '@/shared/types/isRecord'
import type { Order } from '../domain/Order'
import type { OrderItem } from '../domain/OrderItem'
import type { OrderPage } from '../domain/OrderPage'
import type { OrderStatus } from '../domain/OrderStatus'
import type { PostalAddress } from '../domain/PostalAddress'

const ORDER_STATUSES: OrderStatus[] = ['pending', 'cancelled', 'paid']

function isOrderStatus(value: unknown): value is OrderStatus {
  return typeof value === 'string' && ORDER_STATUSES.includes(value as OrderStatus)
}

export function mapOrderItem(payload: unknown): OrderItem {
  if (
    !isRecord(payload) ||
    typeof payload.productId !== 'string' ||
    typeof payload.name !== 'string' ||
    typeof payload.quantity !== 'number'
  ) {
    throw new InvalidResponseError('Invalid order item payload.')
  }

  return {
    productId: payload.productId,
    name: payload.name,
    quantity: payload.quantity,
    unitPrice: mapMoney(payload.unitPrice, 'Invalid order item price.'),
    lineTotal: mapMoney(payload.lineTotal, 'Invalid order item total.'),
  }
}

function mapPostalAddress(payload: unknown): PostalAddress {
  if (
    !isRecord(payload) ||
    typeof payload.recipient !== 'string' ||
    typeof payload.line1 !== 'string' ||
    (payload.line2 !== null && typeof payload.line2 !== 'string') ||
    typeof payload.postalCode !== 'string' ||
    typeof payload.city !== 'string' ||
    typeof payload.country !== 'string'
  ) {
    throw new InvalidResponseError('Invalid postal address payload.')
  }

  return {
    recipient: payload.recipient,
    line1: payload.line1,
    line2: payload.line2,
    postalCode: payload.postalCode,
    city: payload.city,
    country: payload.country,
  }
}

export function mapOrder(payload: unknown): Order {
  if (
    !isRecord(payload) ||
    typeof payload.id !== 'string' ||
    typeof payload.customerId !== 'string' ||
    !isOrderStatus(payload.status) ||
    !Array.isArray(payload.items) ||
    typeof payload.createdAt !== 'string' ||
    (payload.shippingAddress !== null && !isRecord(payload.shippingAddress)) ||
    (payload.billingAddress !== null && !isRecord(payload.billingAddress))
  ) {
    throw new InvalidResponseError('Invalid order payload.')
  }

  return {
    id: payload.id,
    customerId: payload.customerId,
    status: payload.status,
    items: payload.items.map(mapOrderItem),
    total: mapMoney(payload.total, 'Invalid order total.'),
    createdAt: payload.createdAt,
    shippingAddress: payload.shippingAddress === null ? null : mapPostalAddress(payload.shippingAddress),
    billingAddress: payload.billingAddress === null ? null : mapPostalAddress(payload.billingAddress),
  }
}

export function mapOrderPage(payload: unknown): OrderPage {
  if (
    !isRecord(payload) ||
    !Array.isArray(payload.items) ||
    typeof payload.page !== 'number' ||
    typeof payload.limit !== 'number' ||
    typeof payload.total !== 'number'
  ) {
    throw new InvalidResponseError('Invalid order list payload.')
  }

  return {
    items: payload.items.map(mapOrder),
    page: payload.page,
    limit: payload.limit,
    total: payload.total,
  }
}

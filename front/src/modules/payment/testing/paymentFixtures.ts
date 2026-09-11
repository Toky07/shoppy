import type { Payment } from '../domain/Payment'
import { visitorUser } from '@/modules/auth/testing/authFixtures'
import { pendingOrder } from '@/modules/order/testing/orderFixtures'

export const pendingPayment: Payment = {
  id: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
  orderId: pendingOrder.id,
  customerId: visitorUser.id,
  amount: { cents: 3998, currency: 'EUR' },
  status: 'pending',
  createdAt: '2026-09-10T12:05:00+00:00',
  completedAt: null,
}

export const completedPayment: Payment = {
  ...pendingPayment,
  status: 'completed',
  completedAt: '2026-09-10T12:10:00+00:00',
}

export function createPaymentJson(overrides: Record<string, unknown> = {}) {
  return {
    id: pendingPayment.id,
    orderId: pendingPayment.orderId,
    customerId: pendingPayment.customerId,
    amount: { cents: pendingPayment.amount.cents, currency: pendingPayment.amount.currency },
    status: pendingPayment.status,
    createdAt: pendingPayment.createdAt,
    completedAt: pendingPayment.completedAt,
    ...overrides,
  }
}

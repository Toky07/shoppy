import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapMoney } from '@/shared/money/mapMoney'
import { isRecord } from '@/shared/types/isRecord'
import type { Payment } from '../domain/Payment'
import type { PaymentCheckout } from '../domain/PaymentCheckout'
import type { PaymentStatus } from '../domain/PaymentStatus'

const PAYMENT_STATUSES: PaymentStatus[] = ['pending', 'completed', 'cancelled']

function isPaymentStatus(value: unknown): value is PaymentStatus {
  return typeof value === 'string' && PAYMENT_STATUSES.includes(value as PaymentStatus)
}

export function mapPayment(payload: unknown): Payment {
  if (
    !isRecord(payload) ||
    typeof payload.id !== 'string' ||
    typeof payload.orderId !== 'string' ||
    typeof payload.customerId !== 'string' ||
    !isPaymentStatus(payload.status) ||
    typeof payload.createdAt !== 'string' ||
    (payload.completedAt !== null && typeof payload.completedAt !== 'string')
  ) {
    throw new InvalidResponseError('Invalid payment payload.')
  }

  return {
    id: payload.id,
    orderId: payload.orderId,
    customerId: payload.customerId,
    amount: mapMoney(payload.amount, 'Invalid payment amount.'),
    status: payload.status,
    createdAt: payload.createdAt,
    completedAt: payload.completedAt,
  }
}

export function mapPaymentCheckout(payload: unknown): PaymentCheckout {
  if (
    !isRecord(payload) ||
    typeof payload.provider !== 'string' ||
    !isPaymentStatus(payload.status) ||
    typeof payload.completedImmediately !== 'boolean' ||
    (payload.redirectUrl !== null && typeof payload.redirectUrl !== 'string')
  ) {
    throw new InvalidResponseError('Invalid payment checkout payload.')
  }

  return {
    provider: payload.provider,
    status: payload.status,
    completedImmediately: payload.completedImmediately,
    redirectUrl: payload.redirectUrl,
  }
}

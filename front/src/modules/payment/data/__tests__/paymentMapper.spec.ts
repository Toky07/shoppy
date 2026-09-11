import { describe, expect, it } from 'vitest'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapPayment } from '../paymentMapper'
import { createPaymentJson, pendingPayment } from '../../testing/paymentFixtures'

describe('mapPayment', () => {
  it('maps a payment payload', () => {
    expect(mapPayment(createPaymentJson())).toEqual(pendingPayment)
  })

  it('maps a completed payment', () => {
    expect(mapPayment(createPaymentJson({ status: 'completed', completedAt: '2026-09-10T12:10:00+00:00' })).status).toBe(
      'completed',
    )
  })

  it('rejects an invalid payload', () => {
    expect(() => mapPayment({ id: 'x' })).toThrow(InvalidResponseError)
  })
})

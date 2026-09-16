import { describe, expect, it } from 'vitest'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapPayment, mapPaymentCheckout } from '../paymentMapper'
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

describe('mapPaymentCheckout', () => {
  it('maps a hosted stripe checkout', () => {
    expect(
      mapPaymentCheckout({
        provider: 'stripe',
        status: 'pending',
        completedImmediately: false,
        redirectUrl: 'https://checkout.test/cs_test_session',
      }),
    ).toEqual({
      provider: 'stripe',
      status: 'pending',
      completedImmediately: false,
      redirectUrl: 'https://checkout.test/cs_test_session',
    })
  })

  it('rejects an invalid checkout payload', () => {
    expect(() => mapPaymentCheckout({ provider: 'stripe' })).toThrow(InvalidResponseError)
  })
})

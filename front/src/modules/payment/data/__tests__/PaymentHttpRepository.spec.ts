import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { PaymentHttpRepository } from '../PaymentHttpRepository'
import { completedPayment, createPaymentJson, pendingPayment } from '../../testing/paymentFixtures'
import { pendingOrder } from '@/modules/order/testing/orderFixtures'

describe('PaymentHttpRepository', () => {
  it('gets a payment from GET /payments/by-order/:orderId', async () => {
    const http = new FakeHttpClient(() => createPaymentJson())
    const repository = new PaymentHttpRepository(http)

    await expect(repository.getByOrder(pendingOrder.id)).resolves.toEqual(pendingPayment)
    expect(http.calls).toEqual([{ method: 'GET', path: `/payments/by-order/${pendingOrder.id}` }])
  })

  it('completes a payment with POST /payments/complete', async () => {
    const http = new FakeHttpClient(() =>
      createPaymentJson({ status: 'completed', completedAt: completedPayment.completedAt }),
    )
    const repository = new PaymentHttpRepository(http)

    await expect(repository.complete(pendingOrder.id)).resolves.toEqual(completedPayment)
    expect(http.calls).toEqual([
      { method: 'POST', path: '/payments/complete', body: { orderId: pendingOrder.id } },
    ])
  })

  it('starts a stripe checkout with POST /payments/checkout', async () => {
    const http = new FakeHttpClient(() => ({
      provider: 'stripe',
      status: 'pending',
      completedImmediately: false,
      redirectUrl: 'https://checkout.test/cs_test_session',
    }))
    const repository = new PaymentHttpRepository(http)

    await expect(
      repository.startCheckout({
        orderId: pendingOrder.id,
        successUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=success`,
        cancelUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=cancel`,
      }),
    ).resolves.toEqual({
      provider: 'stripe',
      status: 'pending',
      completedImmediately: false,
      redirectUrl: 'https://checkout.test/cs_test_session',
    })
    expect(http.calls).toEqual([
      {
        method: 'POST',
        path: '/payments/checkout',
        body: {
          orderId: pendingOrder.id,
          successUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=success`,
          cancelUrl: `http://localhost:5173/orders/${pendingOrder.id}?payment=cancel`,
        },
      },
    ])
  })

  it('propagates API errors', async () => {
    const http = new FakeHttpClient(() => {
      throw new ApiError(409, 'payment_not_payable', 'Payment is not payable.')
    })
    const repository = new PaymentHttpRepository(http)

    await expect(repository.complete(pendingOrder.id)).rejects.toMatchObject({
      code: 'payment_not_payable',
    })
  })
})

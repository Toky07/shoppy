import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { OrderHttpRepository } from '../OrderHttpRepository'
import { createOrderJson, pendingOrder } from '../../testing/orderFixtures'

describe('OrderHttpRepository', () => {
  it('lists orders from GET /orders', async () => {
    const http = new FakeHttpClient(() => ({
      items: [createOrderJson()],
      page: 1,
      limit: 20,
      total: 1,
    }))
    const repository = new OrderHttpRepository(http)

    const page = await repository.list({ page: 1, limit: 20 })

    expect(http.calls).toEqual([{ method: 'GET', path: '/orders', query: { page: 1, limit: 20 } }])
    expect(page).toEqual({
      items: [pendingOrder],
      page: 1,
      limit: 20,
      total: 1,
    })
  })

  it('gets an order from GET /orders/:id', async () => {
    const http = new FakeHttpClient(() => createOrderJson())
    const repository = new OrderHttpRepository(http)

    await expect(repository.getById(pendingOrder.id)).resolves.toEqual(pendingOrder)
    expect(http.calls).toEqual([{ method: 'GET', path: `/orders/${pendingOrder.id}` }])
  })

  it('lists all orders from GET /admin/orders', async () => {
    const http = new FakeHttpClient(() => ({
      items: [createOrderJson()],
      page: 1,
      limit: 20,
      total: 1,
    }))
    const repository = new OrderHttpRepository(http)

    await repository.listAll({ page: 1, limit: 20 })

    expect(http.calls).toEqual([{ method: 'GET', path: '/admin/orders', query: { page: 1, limit: 20 } }])
  })

  it('marks an order paid with POST /orders/:id/mark-paid', async () => {
    const http = new FakeHttpClient(() => createOrderJson({ status: 'paid' }))
    const repository = new OrderHttpRepository(http)

    await expect(repository.markPaid(pendingOrder.id)).resolves.toMatchObject({ status: 'paid' })
    expect(http.calls).toEqual([{ method: 'POST', path: `/orders/${pendingOrder.id}/mark-paid`, body: undefined }])
  })

  it('cancels an order with POST /orders/:id/cancel', async () => {
    const http = new FakeHttpClient(() => createOrderJson({ status: 'cancelled' }))
    const repository = new OrderHttpRepository(http)

    await expect(repository.cancel(pendingOrder.id)).resolves.toMatchObject({ status: 'cancelled' })
    expect(http.calls).toEqual([{ method: 'POST', path: `/orders/${pendingOrder.id}/cancel`, body: undefined }])
  })

  it('propagates API errors', async () => {
    const http = new FakeHttpClient(() => {
      throw new ApiError(404, 'order_not_found', 'Order not found.')
    })
    const repository = new OrderHttpRepository(http)

    await expect(repository.getById('missing')).rejects.toMatchObject({ code: 'order_not_found' })
  })
})

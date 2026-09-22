import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { CartHttpRepository } from '../CartHttpRepository'
import {
  createCartJson,
  createCheckoutJson,
  filledCart,
  parisCheckout,
  pendingCheckout,
} from '../../testing/cartFixtures'
import { nuvoraTee } from '@/modules/catalog/testing/productFixtures'

describe('CartHttpRepository', () => {
  it('gets the cart from GET /cart', async () => {
    const http = new FakeHttpClient(() => createCartJson())
    const repository = new CartHttpRepository(http)

    await expect(repository.get()).resolves.toEqual(filledCart)
    expect(http.calls).toEqual([{ method: 'GET', path: '/cart' }])
  })

  it('adds an item with POST /cart/items', async () => {
    const http = new FakeHttpClient(() => createCartJson())
    const repository = new CartHttpRepository(http)

    await expect(repository.addItem(nuvoraTee.id, 2)).resolves.toEqual(filledCart)
    expect(http.calls).toEqual([
      { method: 'POST', path: '/cart/items', body: { productId: nuvoraTee.id, quantity: 2 } },
    ])
  })

  it('updates an item with PUT /cart/items/:productId', async () => {
    const http = new FakeHttpClient(() => createCartJson())
    const repository = new CartHttpRepository(http)

    await expect(repository.updateItem(nuvoraTee.id, 3)).resolves.toEqual(filledCart)
    expect(http.calls).toEqual([
      { method: 'PUT', path: `/cart/items/${nuvoraTee.id}`, body: { quantity: 3 } },
    ])
  })

  it('removes an item with DELETE /cart/items/:productId', async () => {
    const http = new FakeHttpClient(() => createCartJson({ items: [], total: { cents: 0, currency: 'EUR' } }))
    const repository = new CartHttpRepository(http)

    await repository.removeItem(nuvoraTee.id)

    expect(http.calls).toEqual([{ method: 'DELETE', path: `/cart/items/${nuvoraTee.id}` }])
  })

  it('clears the cart with DELETE /cart', async () => {
    const http = new FakeHttpClient(() => createCartJson({ id: null, items: [], total: { cents: 0, currency: 'EUR' } }))
    const repository = new CartHttpRepository(http)

    await repository.clear()

    expect(http.calls).toEqual([{ method: 'DELETE', path: '/cart' }])
  })

  it('checks out with POST /cart/checkout', async () => {
    const http = new FakeHttpClient(() => createCheckoutJson())
    const repository = new CartHttpRepository(http)

    await expect(repository.checkout(parisCheckout)).resolves.toEqual(pendingCheckout)
    expect(http.calls).toEqual([{ method: 'POST', path: '/cart/checkout', body: parisCheckout }])
  })

  it('propagates API errors', async () => {
    const http = new FakeHttpClient(() => {
      throw new ApiError(409, 'insufficient_product_stock', 'Not enough stock.')
    })
    const repository = new CartHttpRepository(http)

    await expect(repository.addItem(nuvoraTee.id, 99)).rejects.toMatchObject({
      code: 'insufficient_product_stock',
    })
  })
})

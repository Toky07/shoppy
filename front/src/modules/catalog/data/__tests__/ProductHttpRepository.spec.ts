import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { ProductHttpRepository } from '../ProductHttpRepository'
import { createProductJson, nuvoraTee } from '../../testing/productFixtures'

describe('ProductHttpRepository', () => {
  it('lists products from GET /products', async () => {
    const http = new FakeHttpClient(() => ({
      items: [createProductJson()],
      page: 2,
      limit: 20,
      total: 21,
    }))
    const repository = new ProductHttpRepository(http)

    const page = await repository.list({ page: 2, limit: 20 })

    expect(http.calls).toEqual([{ method: 'GET', path: '/products', query: { page: 2, limit: 20 } }])
    expect(page).toEqual({
      items: [nuvoraTee],
      page: 2,
      limit: 20,
      total: 21,
    })
  })

  it('forwards search and sort query parameters', async () => {
    const http = new FakeHttpClient(() => ({
      items: [createProductJson()],
      page: 1,
      limit: 20,
      total: 1,
    }))
    const repository = new ProductHttpRepository(http)

    await repository.list({ page: 1, limit: 20, search: 'hoodie', sort: 'price_asc' })

    expect(http.calls).toEqual([
      { method: 'GET', path: '/products', query: { page: 1, limit: 20, q: 'hoodie', sort: 'price_asc' } },
    ])
  })

  it('gets a product from GET /products/:slug', async () => {
    const http = new FakeHttpClient(() => createProductJson())
    const repository = new ProductHttpRepository(http)

    await expect(repository.getById(nuvoraTee.slug)).resolves.toEqual(nuvoraTee)
    expect(http.calls).toEqual([{ method: 'GET', path: `/products/${nuvoraTee.slug}` }])
  })

  it('propagates API errors', async () => {
    const http = new FakeHttpClient(() => {
      throw new ApiError(404, 'product_not_found', 'Product not found.')
    })
    const repository = new ProductHttpRepository(http)

    await expect(repository.getById('missing')).rejects.toMatchObject({
      code: 'product_not_found',
    })
  })
})

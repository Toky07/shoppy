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

  it('forwards price and stock filters', async () => {
    const http = new FakeHttpClient(() => ({
      items: [],
      page: 1,
      limit: 20,
      total: 0,
    }))
    const repository = new ProductHttpRepository(http)

    await repository.list({
      page: 1,
      limit: 20,
      minPriceCents: 1500,
      maxPriceCents: 3000,
      inStockOnly: true,
    })

    expect(http.calls).toEqual([
      {
        method: 'GET',
        path: '/products',
        query: { page: 1, limit: 20, minPrice: 1500, maxPrice: 3000, inStock: 1 },
      },
    ])
  })

  it('forwards the category filter', async () => {
    const http = new FakeHttpClient(() => ({
      items: [],
      page: 1,
      limit: 20,
      total: 0,
    }))
    const repository = new ProductHttpRepository(http)

    await repository.list({ page: 1, limit: 20, categorySlug: 'textile' })

    expect(http.calls).toEqual([
      { method: 'GET', path: '/products', query: { page: 1, limit: 20, category: 'textile' } },
    ])
  })

  it('lists categories from GET /categories', async () => {
    const http = new FakeHttpClient(() => ({
      items: [{ id: '550e8400-e29b-41d4-a716-446655440010', name: 'Textile', slug: 'textile' }],
    }))
    const repository = new ProductHttpRepository(http)

    await expect(repository.listCategories()).resolves.toEqual([
      { id: '550e8400-e29b-41d4-a716-446655440010', name: 'Textile', slug: 'textile' },
    ])
    expect(http.calls).toEqual([{ method: 'GET', path: '/categories' }])
  })

  it('loads products by id in one request', async () => {
    const http = new FakeHttpClient(() => ({
      items: [createProductJson()],
      page: 1,
      limit: 1,
      total: 1,
    }))
    const repository = new ProductHttpRepository(http)

    await expect(repository.listByIds([nuvoraTee.id])).resolves.toEqual([nuvoraTee])
    expect(http.calls).toEqual([
      { method: 'GET', path: '/products', query: { ids: nuvoraTee.id } },
    ])
  })

  it('skips the request when there are no ids', async () => {
    const http = new FakeHttpClient(() => {
      throw new Error('unexpected request')
    })
    const repository = new ProductHttpRepository(http)

    await expect(repository.listByIds([])).resolves.toEqual([])
    expect(http.calls).toEqual([])
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

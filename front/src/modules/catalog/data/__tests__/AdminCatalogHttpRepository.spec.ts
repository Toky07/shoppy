import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { AdminCatalogHttpRepository } from '../AdminCatalogHttpRepository'
import { createProductJson, nuvoraTee } from '../../testing/productFixtures'

describe('AdminCatalogHttpRepository', () => {
  it('creates a product with POST /products', async () => {
    const http = new FakeHttpClient(() => createProductJson())
    const repository = new AdminCatalogHttpRepository(http)

    await expect(
      repository.create({
        name: nuvoraTee.name,
        priceCents: 1999,
        description: nuvoraTee.description,
        stock: 10,
      }),
    ).resolves.toEqual(nuvoraTee)
    expect(http.calls).toEqual([
      {
        method: 'POST',
        path: '/products',
        body: {
          name: nuvoraTee.name,
          priceCents: 1999,
          description: nuvoraTee.description,
          stock: 10,
        },
      },
    ])
  })

  it('updates a product with PATCH /products/:id', async () => {
    const http = new FakeHttpClient(() => createProductJson({ name: 'Tee 2' }))
    const repository = new AdminCatalogHttpRepository(http)

    await repository.update(nuvoraTee.id, { name: 'Tee 2', priceCents: 1999, description: null })

    expect(http.calls).toEqual([
      {
        method: 'PATCH',
        path: `/products/${nuvoraTee.id}`,
        body: { name: 'Tee 2', priceCents: 1999, description: null },
      },
    ])
  })

  it('sets stock with PUT /products/:id/stock', async () => {
    const http = new FakeHttpClient(() => createProductJson({ stock: 4 }))
    const repository = new AdminCatalogHttpRepository(http)

    await repository.setStock(nuvoraTee.id, 4)

    expect(http.calls).toEqual([
      { method: 'PUT', path: `/products/${nuvoraTee.id}/stock`, body: { stock: 4 } },
    ])
  })

  it('deletes a product with DELETE /products/:id', async () => {
    const http = new FakeHttpClient(() => undefined)
    const repository = new AdminCatalogHttpRepository(http)

    await repository.delete(nuvoraTee.id)

    expect(http.calls).toEqual([{ method: 'DELETE', path: `/products/${nuvoraTee.id}` }])
  })
})

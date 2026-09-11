import { describe, expect, it, vi } from 'vitest'
import { listProducts } from '../listProducts'
import { createFakeCatalogRepository } from '../../testing/fakeCatalogRepository'
import { nuvoraTee } from '../../testing/productFixtures'

describe('listProducts', () => {
  it('delegates to the catalog repository', async () => {
    const repository = createFakeCatalogRepository([nuvoraTee])
    const list = vi.spyOn(repository, 'list')

    await expect(listProducts(repository, { page: 1, limit: 20 })).resolves.toEqual({
      items: [nuvoraTee],
      page: 1,
      limit: 20,
      total: 1,
    })
    expect(list).toHaveBeenCalledWith({ page: 1, limit: 20 })
  })
})

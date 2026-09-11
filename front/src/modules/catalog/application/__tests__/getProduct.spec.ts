import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { getProduct } from '../getProduct'
import { createFakeCatalogRepository } from '../../testing/fakeCatalogRepository'
import { nuvoraTee } from '../../testing/productFixtures'

describe('getProduct', () => {
  it('returns the product from the repository', async () => {
    const repository = createFakeCatalogRepository([nuvoraTee])

    await expect(getProduct(repository, nuvoraTee.id)).resolves.toEqual(nuvoraTee)
  })

  it('propagates a missing product error', async () => {
    const repository = createFakeCatalogRepository([nuvoraTee])

    await expect(getProduct(repository, 'missing')).rejects.toBeInstanceOf(ApiError)
  })
})

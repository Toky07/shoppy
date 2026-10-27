import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { FavoriteHttpRepository } from '../FavoriteHttpRepository'
import { nuvoraTee, outOfStockMug } from '../../testing/productFixtures'

describe('FavoriteHttpRepository', () => {
  it('lists favorites from GET /favorites', async () => {
    const http = new FakeHttpClient(() => ({ productIds: [nuvoraTee.id] }))
    const repository = new FavoriteHttpRepository(http)

    await expect(repository.list()).resolves.toEqual([nuvoraTee.id])
    expect(http.calls).toEqual([{ method: 'GET', path: '/favorites' }])
  })

  it('adds a favorite with POST /favorites', async () => {
    const http = new FakeHttpClient(() => ({ productIds: [nuvoraTee.id] }))
    const repository = new FavoriteHttpRepository(http)

    await expect(repository.add(nuvoraTee.id)).resolves.toEqual([nuvoraTee.id])
    expect(http.calls).toEqual([{ method: 'POST', path: '/favorites', body: { productId: nuvoraTee.id } }])
  })

  it('removes a favorite with DELETE /favorites/:productId', async () => {
    const http = new FakeHttpClient(() => ({ productIds: [] }))
    const repository = new FavoriteHttpRepository(http)

    await expect(repository.remove(nuvoraTee.id)).resolves.toEqual([])
    expect(http.calls).toEqual([{ method: 'DELETE', path: `/favorites/${nuvoraTee.id}` }])
  })

  it('clears favorites with DELETE /favorites', async () => {
    const http = new FakeHttpClient(() => ({ productIds: [] }))
    const repository = new FavoriteHttpRepository(http)

    await expect(repository.clear()).resolves.toEqual([])
    expect(http.calls).toEqual([{ method: 'DELETE', path: '/favorites' }])
  })

  it('merges guest favorites with POST /favorites/merge', async () => {
    const http = new FakeHttpClient(() => ({ productIds: [outOfStockMug.id, nuvoraTee.id] }))
    const repository = new FavoriteHttpRepository(http)

    await expect(repository.merge([outOfStockMug.id])).resolves.toEqual([outOfStockMug.id, nuvoraTee.id])
    expect(http.calls).toEqual([
      { method: 'POST', path: '/favorites/merge', body: { productIds: [outOfStockMug.id] } },
    ])
  })
})

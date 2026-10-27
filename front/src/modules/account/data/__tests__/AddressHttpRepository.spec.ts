import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { AddressHttpRepository } from '../AddressHttpRepository'
import { lyonAddress, parisAddress } from '../../testing/addressFixtures'

describe('AddressHttpRepository', () => {
  it('lists addresses from GET /addresses', async () => {
    const http = new FakeHttpClient(() => ({ addresses: [parisAddress] }))
    const repository = new AddressHttpRepository(http)

    await expect(repository.list()).resolves.toEqual([parisAddress])
    expect(http.calls).toEqual([{ method: 'GET', path: '/addresses' }])
  })

  it('removes an address with DELETE /addresses/:id', async () => {
    const http = new FakeHttpClient(() => ({ addresses: [] }))
    const repository = new AddressHttpRepository(http)

    await expect(repository.remove(parisAddress.id)).resolves.toEqual([])
    expect(http.calls).toEqual([{ method: 'DELETE', path: `/addresses/${parisAddress.id}` }])
  })

  it('marks a default address with POST /addresses/:id/default', async () => {
    const http = new FakeHttpClient(() => ({ addresses: [{ ...lyonAddress, isDefault: true }] }))
    const repository = new AddressHttpRepository(http)

    await expect(repository.markDefault(lyonAddress.id)).resolves.toEqual([
      { ...lyonAddress, isDefault: true },
    ])
    expect(http.calls).toEqual([
      { method: 'POST', path: `/addresses/${lyonAddress.id}/default`, body: undefined },
    ])
  })
})

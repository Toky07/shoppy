import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { outOfStockMug } from '@/modules/catalog/testing/productFixtures'
import { AdminDashboardHttpRepository } from '../AdminDashboardHttpRepository'

const payload = {
  revenue: { cents: 12345, currency: 'EUR' },
  today: { orders: 3, revenue: { cents: 4500, currency: 'EUR' } },
  lowStock: [
    {
      id: outOfStockMug.id,
      name: outOfStockMug.name,
      slug: outOfStockMug.slug,
      stock: 0,
    },
  ],
}

describe('AdminDashboardHttpRepository', () => {
  it('loads the dashboard from GET /admin/dashboard', async () => {
    const http = new FakeHttpClient(() => payload)
    const repository = new AdminDashboardHttpRepository(http)

    await expect(repository.get()).resolves.toEqual(payload)
    expect(http.calls).toEqual([{ method: 'GET', path: '/admin/dashboard' }])
  })

  it('rejects an invalid payload', async () => {
    const http = new FakeHttpClient(() => ({ revenue: 0 }))
    const repository = new AdminDashboardHttpRepository(http)

    await expect(repository.get()).rejects.toBeInstanceOf(InvalidResponseError)
  })
})

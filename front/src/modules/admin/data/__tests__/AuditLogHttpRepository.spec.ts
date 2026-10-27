import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { AuditLogHttpRepository } from '../AuditLogHttpRepository'

const entry = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1',
  action: 'role_changed',
  actorId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9',
  actorEmail: 'admin@nuvora.test',
  subjectType: 'user',
  subjectId: '11111111-1111-4111-8111-111111111111',
  metadata: { from: 'customer', to: 'admin' },
  createdAt: '2026-10-27T12:00:00+00:00',
}

describe('AuditLogHttpRepository', () => {
  it('lists the audit log from GET /admin/audit', async () => {
    const http = new FakeHttpClient(() => ({
      items: [entry],
      page: 1,
      limit: 20,
      total: 1,
    }))
    const repository = new AuditLogHttpRepository(http)

    await expect(repository.list({ page: 1, limit: 20 })).resolves.toEqual({
      items: [entry],
      page: 1,
      limit: 20,
      total: 1,
    })
    expect(http.calls).toEqual([{ method: 'GET', path: '/admin/audit', query: { page: 1, limit: 20 } }])
  })

  it('rejects an invalid payload', async () => {
    const http = new FakeHttpClient(() => ({ items: [] }))
    const repository = new AuditLogHttpRepository(http)

    await expect(repository.list({ page: 1, limit: 20 })).rejects.toBeInstanceOf(InvalidResponseError)
  })
})

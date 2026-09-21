import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { UserHttpDirectory } from '../UserHttpDirectory'
import { createUserJson, visitorUser } from '../../testing/authFixtures'

describe('UserHttpDirectory', () => {
  it('gets a user from GET /users/:id', async () => {
    const http = new FakeHttpClient(() => createUserJson())
    const directory = new UserHttpDirectory(http)

    await expect(directory.getById(visitorUser.id)).resolves.toEqual(visitorUser)
    expect(http.calls).toEqual([{ method: 'GET', path: `/users/${visitorUser.id}` }])
  })

  it('assigns a role with PATCH /users/:id', async () => {
    const http = new FakeHttpClient(() => createUserJson({ role: 'admin' }))
    const directory = new UserHttpDirectory(http)

    await expect(directory.assignRole(visitorUser.id, 'admin')).resolves.toMatchObject({ role: 'admin' })
    expect(http.calls).toEqual([
      { method: 'PATCH', path: `/users/${visitorUser.id}`, body: { role: 'admin' } },
    ])
  })

  it('lists users from GET /admin/users', async () => {
    const http = new FakeHttpClient(() => ({
      items: [createUserJson()],
      page: 1,
      limit: 20,
      total: 1,
    }))
    const directory = new UserHttpDirectory(http)

    await expect(directory.list({ page: 1, limit: 20, search: 'visitor' })).resolves.toEqual({
      items: [visitorUser],
      page: 1,
      limit: 20,
      total: 1,
    })
    expect(http.calls).toEqual([
      { method: 'GET', path: '/admin/users', query: { page: 1, limit: 20, q: 'visitor' } },
    ])
  })
})

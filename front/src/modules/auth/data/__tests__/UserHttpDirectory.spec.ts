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
})

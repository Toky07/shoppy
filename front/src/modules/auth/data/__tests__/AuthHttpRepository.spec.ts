import { describe, expect, it } from 'vitest'
import { FakeHttpClient } from '@/shared/testing/FakeHttpClient'
import { AuthHttpRepository } from '../AuthHttpRepository'
import { createUserJson, visitorSession, visitorUser } from '../../testing/authFixtures'

describe('AuthHttpRepository', () => {
  it('logs in via POST /auth/login', async () => {
    const http = new FakeHttpClient(() => ({
      accessToken: visitorSession.accessToken,
      user: createUserJson(),
    }))
    const repository = new AuthHttpRepository(http)

    await expect(
      repository.login({ email: 'visitor@shoppy.test', password: 'password123' }),
    ).resolves.toEqual(visitorSession)
    expect(http.calls).toEqual([
      {
        method: 'POST',
        path: '/auth/login',
        body: { email: 'visitor@shoppy.test', password: 'password123' },
      },
    ])
  })

  it('registers via POST /users', async () => {
    const http = new FakeHttpClient(() => createUserJson())
    const repository = new AuthHttpRepository(http)

    await expect(
      repository.register({ email: 'visitor@shoppy.test', password: 'password123' }),
    ).resolves.toEqual(visitorUser)
    expect(http.calls[0]).toMatchObject({ method: 'POST', path: '/users' })
  })

  it('logs out via POST /auth/logout', async () => {
    const http = new FakeHttpClient(() => undefined)
    const repository = new AuthHttpRepository(http)

    await repository.logout()

    expect(http.calls).toEqual([{ method: 'POST', path: '/auth/logout' }])
  })
})

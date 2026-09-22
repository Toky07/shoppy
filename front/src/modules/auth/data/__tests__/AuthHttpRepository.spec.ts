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

  it('calls the account security endpoints', async () => {
    const http = new FakeHttpClient(() => undefined)
    const repository = new AuthHttpRepository(http)

    await repository.requestPasswordReset('ada@shoppy.test')
    await repository.resetPassword('token', 'brand-new-secret')
    await repository.verifyEmail('token')
    await repository.requestEmailVerification()
    await repository.changePassword('secret-secret', 'brand-new-secret')
    await repository.requestEmailChange('ada.new@shoppy.test', 'secret-secret')
    await repository.confirmEmailChange('token')
    await repository.logoutAll()
    await repository.deleteAccount('secret-secret')

    expect(http.calls).toEqual([
      { method: 'POST', path: '/auth/password-resets', body: { email: 'ada@shoppy.test' } },
      { method: 'POST', path: '/auth/password-resets/confirm', body: { token: 'token', password: 'brand-new-secret' } },
      { method: 'POST', path: '/auth/email-verifications', body: { token: 'token' } },
      { method: 'POST', path: '/auth/email-verifications/request' },
      {
        method: 'POST',
        path: '/auth/password',
        body: { currentPassword: 'secret-secret', newPassword: 'brand-new-secret' },
      },
      {
        method: 'POST',
        path: '/auth/email-changes',
        body: { email: 'ada.new@shoppy.test', currentPassword: 'secret-secret' },
      },
      { method: 'POST', path: '/auth/email-changes/confirm', body: { token: 'token' } },
      { method: 'POST', path: '/auth/logout-all' },
      { method: 'POST', path: '/auth/account/deletion', body: { password: 'secret-secret' } },
    ])
  })
})

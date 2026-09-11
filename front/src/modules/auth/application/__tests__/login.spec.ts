import { describe, expect, it } from 'vitest'
import { login } from '../login'
import { FakeAuthRepository } from '../../testing/FakeAuthRepository'
import { MemorySessionStore } from '../../testing/MemorySessionStore'
import { createAuthSession } from '../createAuthSession'
import { ApiError } from '@/shared/http/ApiError'

describe('login', () => {
  it('stores the session after a successful login', async () => {
    const repository = new FakeAuthRepository()
    const session = createAuthSession(new MemorySessionStore())

    await login(repository, session, { email: 'Visitor@shoppy.test', password: 'password123' })

    expect(session.session.value?.user.email).toBe('visitor@shoppy.test')
    expect(session.current()).toHaveLength(64)
  })

  it('does not store a session when login fails', async () => {
    const repository = new FakeAuthRepository()
    repository.loginError = new ApiError(401, 'invalid_credentials', 'Invalid credentials.')
    const session = createAuthSession(new MemorySessionStore())

    await expect(
      login(repository, session, { email: 'visitor@shoppy.test', password: 'wrong-password' }),
    ).rejects.toMatchObject({ code: 'invalid_credentials' })
    expect(session.session.value).toBeNull()
  })
})

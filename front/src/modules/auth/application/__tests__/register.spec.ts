import { describe, expect, it } from 'vitest'
import { register } from '../register'
import { FakeAuthRepository } from '../../testing/FakeAuthRepository'
import { MemorySessionStore } from '../../testing/MemorySessionStore'
import { createAuthSession } from '../createAuthSession'

describe('register', () => {
  it('registers then logs the user in', async () => {
    const repository = new FakeAuthRepository()
    const session = createAuthSession(new MemorySessionStore())

    await register(repository, session, { email: 'Ada@shoppy.test', password: 'password123' })

    expect(repository.registrations).toHaveLength(1)
    expect(repository.logins).toHaveLength(1)
    expect(session.session.value?.user.email).toBe('ada@shoppy.test')
  })
})

import { describe, expect, it } from 'vitest'
import { logout } from '../logout'
import { FakeAuthRepository } from '../../testing/FakeAuthRepository'
import { MemorySessionStore } from '../../testing/MemorySessionStore'
import { createAuthSession } from '../createAuthSession'
import { visitorSession } from '../../testing/authFixtures'
import { ApiError } from '@/shared/http/ApiError'
import { createMemorySessionPresenceStore } from '../../data/sessionPresenceStore'

describe('logout', () => {
  it('clears the session after logout', async () => {
    const repository = new FakeAuthRepository()
    const session = createAuthSession(new MemorySessionStore(visitorSession))

    await logout(repository, session)

    expect(repository.logoutCount).toBe(1)
    expect(session.session.value).toBeNull()
  })

  it('clears the session even if the API call fails', async () => {
    const repository = new FakeAuthRepository()
    repository.logout = async () => {
      throw new ApiError(401, 'unauthenticated', 'Unauthenticated.')
    }
    const session = createAuthSession(new MemorySessionStore(visitorSession))

    await logout(repository, session)

    expect(session.session.value).toBeNull()
  })

  it('forgets the visit so an anonymous reload does not probe the API', async () => {
    const repository = new FakeAuthRepository()
    const presence = createMemorySessionPresenceStore(true)
    const session = createAuthSession(presence)
    session.set(visitorSession)

    await logout(repository, session)

    expect(presence.remembered()).toBe(false)
  })
})

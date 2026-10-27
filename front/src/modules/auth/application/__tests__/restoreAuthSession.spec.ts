import { describe, expect, it } from 'vitest'
import { ApiError } from '@/shared/http/ApiError'
import { createAuthSession } from '../createAuthSession'
import { restoreAuthSession } from '../restoreAuthSession'
import { FakeAuthRepository } from '../../testing/FakeAuthRepository'
import { visitorUser } from '../../testing/authFixtures'
import { createMemorySessionPresenceStore } from '../../data/sessionPresenceStore'

describe('restoreAuthSession', () => {
  it('does not probe the API for an anonymous visit', async () => {
    const repository = new FakeAuthRepository()
    const presence = createMemorySessionPresenceStore()
    const session = createAuthSession(presence)

    await restoreAuthSession(repository, session, presence.remembered())

    expect(repository.currentUserCalls).toBe(0)
    expect(session.session.value).toBeNull()
  })

  it('restores the user when a previous visit was signed in', async () => {
    const repository = new FakeAuthRepository()
    const presence = createMemorySessionPresenceStore(true)
    const session = createAuthSession(presence)

    await restoreAuthSession(repository, session, presence.remembered())

    expect(repository.currentUserCalls).toBe(1)
    expect(session.session.value?.user).toEqual(visitorUser)
  })

  it('forgets the hint when restore fails', async () => {
    const repository = new FakeAuthRepository()
    repository.currentUserError = new ApiError(401, 'unauthenticated', 'Unauthenticated.')
    const presence = createMemorySessionPresenceStore(true)
    const session = createAuthSession(presence)

    await restoreAuthSession(repository, session, presence.remembered())

    expect(session.session.value).toBeNull()
    expect(presence.remembered()).toBe(false)
  })
})

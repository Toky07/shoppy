import { describe, expect, it } from 'vitest'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapSession } from '../sessionMapper'
import { createUserJson, visitorSession } from '../../testing/authFixtures'

describe('mapSession', () => {
  it('maps a login payload', () => {
    expect(
      mapSession({
        accessToken: visitorSession.accessToken,
        user: createUserJson(),
      }),
    ).toEqual(visitorSession)
  })

  it('rejects a payload without a token', () => {
    expect(() => mapSession({ user: createUserJson() })).toThrow(InvalidResponseError)
  })
})

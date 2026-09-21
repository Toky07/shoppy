import { describe, expect, it } from 'vitest'
import { mapUser, mapUserPage } from '../userMapper'
import { createUserJson, visitorUser } from '../../testing/authFixtures'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'

describe('mapUser', () => {
  it('maps a user payload', () => {
    expect(mapUser(createUserJson())).toEqual(visitorUser)
  })

  it('rejects an invalid payload', () => {
    expect(() => mapUser({ email: 'a@b.c' })).toThrow(InvalidResponseError)
  })

  it('maps a user list payload', () => {
    expect(
      mapUserPage({
        items: [createUserJson()],
        page: 1,
        limit: 20,
        total: 1,
      }),
    ).toEqual({
      items: [visitorUser],
      page: 1,
      limit: 20,
      total: 1,
    })
  })
})

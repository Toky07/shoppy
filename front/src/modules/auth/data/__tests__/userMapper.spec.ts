import { describe, expect, it } from 'vitest'
import { mapUser } from '../userMapper'
import { createUserJson, visitorUser } from '../../testing/authFixtures'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'

describe('mapUser', () => {
  it('maps a user payload', () => {
    expect(mapUser(createUserJson())).toEqual(visitorUser)
  })

  it('rejects an invalid payload', () => {
    expect(() => mapUser({ email: 'a@b.c' })).toThrow(InvalidResponseError)
  })
})

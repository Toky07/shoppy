import { describe, expect, it, vi } from 'vitest'
import {
  accountLinkToken,
  concealAccountLinkToken,
  exposesAccountToken,
} from '../accountLinkToken'

describe('accountLinkToken', () => {
  it('reads the token from the fragment', () => {
    expect(accountLinkToken({ query: {}, hash: '#token=reset-token' })).toBe('reset-token')
  })

  it('falls back to a query token from older emails', () => {
    expect(accountLinkToken({ query: { token: 'legacy-token' }, hash: '' })).toBe('legacy-token')
  })

  it('prefers the fragment over the query', () => {
    expect(
      accountLinkToken({ query: { token: 'query-token' }, hash: '#token=hash-token' }),
    ).toBe('hash-token')
  })

  it('is empty when the link has no token', () => {
    expect(accountLinkToken({ query: {}, hash: '' })).toBe('')
    expect(exposesAccountToken({ query: {}, hash: '' })).toBe(false)
  })
})

describe('concealAccountLinkToken', () => {
  it('strips token from hash and query so it leaves history and referer', async () => {
    const replace = vi.fn<() => Promise<unknown>>().mockResolvedValue(undefined)

    await concealAccountLinkToken(
      { replace },
      { path: '/reset-password', query: { token: 'legacy-token' }, hash: '#token=reset-token' },
    )

    expect(replace).toHaveBeenCalledWith({
      path: '/reset-password',
      query: {},
      hash: '',
    })
  })

  it('does nothing when the url is already clean', async () => {
    const replace = vi.fn<() => Promise<unknown>>()

    await concealAccountLinkToken({ replace }, { path: '/reset-password', query: {}, hash: '' })

    expect(replace).not.toHaveBeenCalled()
  })
})

import { describe, expect, it } from 'vitest'
import { formatDate } from '../formatDate'

describe('formatDate', () => {
  it('formats an ISO date in French UTC', () => {
    expect(formatDate('2026-09-10T12:05:00+00:00')).toBe('10 septembre 2026')
  })

  it('formats an ISO date in English UTC', () => {
    expect(formatDate('2026-09-10T12:05:00+00:00', 'en-GB')).toBe('10 September 2026')
  })
})

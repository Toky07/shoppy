import { describe, expect, it } from 'vitest'
import { parseSearchQuery } from '../parseSearchQuery'

describe('parseSearchQuery', () => {
  it('returns a trimmed search string', () => {
    expect(parseSearchQuery('  hoodie  ')).toBe('hoodie')
    expect(parseSearchQuery(['tee'])).toBe('tee')
  })

  it('falls back to an empty string', () => {
    expect(parseSearchQuery(undefined)).toBe('')
    expect(parseSearchQuery(1)).toBe('')
    expect(parseSearchQuery([''])).toBe('')
  })
})

import { describe, expect, it } from 'vitest'
import { parsePageQuery } from '../parsePageQuery'

describe('parsePageQuery', () => {
  it('returns a positive integer page', () => {
    expect(parsePageQuery('3')).toBe(3)
    expect(parsePageQuery(['2'])).toBe(2)
  })

  it('falls back to 1 for invalid values', () => {
    expect(parsePageQuery(undefined)).toBe(1)
    expect(parsePageQuery('0')).toBe(1)
    expect(parsePageQuery('nope')).toBe(1)
    expect(parsePageQuery(['-1'])).toBe(1)
  })
})

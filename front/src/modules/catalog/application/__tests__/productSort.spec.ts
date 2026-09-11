import { describe, expect, it } from 'vitest'
import { parseProductSort } from '../productSort'

describe('parseProductSort', () => {
  it('returns a known sort', () => {
    expect(parseProductSort('price_asc')).toBe('price_asc')
    expect(parseProductSort(['name_asc'])).toBe('name_asc')
  })

  it('falls back to newest', () => {
    expect(parseProductSort(undefined)).toBe('newest')
    expect(parseProductSort('popularity')).toBe('newest')
  })
})

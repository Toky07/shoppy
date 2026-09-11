import { describe, expect, it } from 'vitest'
import { joinUrl } from '../joinUrl'

describe('joinUrl', () => {
  it('joins base and path without duplicate slashes', () => {
    expect(joinUrl('/api/', '/products')).toBe('/api/products')
    expect(joinUrl('https://api.test', 'products')).toBe('https://api.test/products')
  })
})

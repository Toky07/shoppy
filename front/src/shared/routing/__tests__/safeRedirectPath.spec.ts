import { describe, expect, it } from 'vitest'
import { safeRedirectPath } from '../safeRedirectPath'

describe('safeRedirectPath', () => {
  it('accepts a same-origin path', () => {
    expect(safeRedirectPath('/products/1')).toBe('/products/1')
  })

  it('rejects an open redirect', () => {
    expect(safeRedirectPath('https://evil.test')).toBe('/')
    expect(safeRedirectPath('//evil.test')).toBe('/')
  })
})

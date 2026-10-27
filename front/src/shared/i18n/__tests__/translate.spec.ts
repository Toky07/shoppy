import { describe, expect, it } from 'vitest'
import { translateMessage, translateFr } from '../catalogs'

describe('translate', () => {
  it('returns french copy by default', () => {
    expect(translateFr('nav.catalog')).toBe('Catalogue')
    expect(translateFr('header.cartCount', { count: 2 })).toBe('Panier (2)')
  })

  it('returns english copy', () => {
    expect(translateMessage('en', 'nav.catalog')).toBe('Catalog')
    expect(translateMessage('en', 'header.cartCount', { count: 2 })).toBe('Cart (2)')
  })

  it('falls back to the key when missing', () => {
    expect(translateFr('does.not.exist')).toBe('does.not.exist')
  })
})

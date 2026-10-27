import { afterEach, describe, expect, it } from 'vitest'
import { applyPageMeta, resetPageMeta } from '../applyPageMeta'

afterEach(() => {
  resetPageMeta()
})

describe('applyPageMeta', () => {
  it('sets the document title, description and open graph tags', () => {
    applyPageMeta({
      title: 'Nuvora Tee — Shoppy',
      description: 'Soft cotton tee',
      image: '/media/products/nuvora-tee.svg',
      path: '/products/nuvora-tee',
      type: 'product',
    })

    expect(document.title).toBe('Nuvora Tee — Shoppy')
    expect(document.querySelector('meta[name="description"]')?.getAttribute('content')).toBe('Soft cotton tee')
    expect(document.querySelector('meta[property="og:title"]')?.getAttribute('content')).toBe(
      'Nuvora Tee — Shoppy',
    )
    expect(document.querySelector('meta[property="og:description"]')?.getAttribute('content')).toBe(
      'Soft cotton tee',
    )
    expect(document.querySelector('meta[property="og:type"]')?.getAttribute('content')).toBe('product')
    expect(document.querySelector('meta[property="og:image"]')?.getAttribute('content')).toBe(
      `${window.location.origin}/media/products/nuvora-tee.svg`,
    )
    expect(document.querySelector('link[rel="canonical"]')?.getAttribute('href')).toBe(
      `${window.location.origin}/products/nuvora-tee`,
    )
  })

  it('restores the storefront defaults', () => {
    applyPageMeta({
      title: 'Nuvora Tee — Shoppy',
      description: 'Soft cotton tee',
      path: '/products/nuvora-tee',
      type: 'product',
    })

    resetPageMeta()

    expect(document.title).toBe('Shoppy — Boutique')
    expect(document.querySelector('meta[name="description"]')?.getAttribute('content')).toBe(
      "Shoppy — une sélection d'objets utiles, livrés vite et payés en toute sécurité.",
    )
    expect(document.querySelector('meta[property="og:type"]')?.getAttribute('content')).toBe('website')
  })
})

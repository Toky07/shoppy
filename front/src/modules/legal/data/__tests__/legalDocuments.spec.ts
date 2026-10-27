import { describe, expect, it } from 'vitest'
import { legalDocumentByPath, legalDocuments } from '../legalDocuments'

describe('legalDocuments', () => {
  it('lists the four storefront legal pages', () => {
    expect(legalDocuments.map((page) => [page.slug, page.path, page.navLabel])).toEqual([
      ['cgv', '/cgv', 'CGV'],
      ['confidentialite', '/confidentialite', 'Confidentialité'],
      ['mentions', '/mentions', 'Mentions légales'],
      ['retours', '/retours', 'Retours'],
    ])
  })

  it('looks up a legal page by path', () => {
    expect(legalDocumentByPath('/cgv')?.title).toBe('Conditions générales de vente')
    expect(legalDocumentByPath('/confidentialite')?.title).toBe('Politique de confidentialité')
    expect(legalDocumentByPath('/confidentialite')?.sections.map((section) => section.heading)).toContain(
      'Cookies',
    )
    expect(legalDocumentByPath('/mentions')?.title).toBe('Mentions légales')
    expect(legalDocumentByPath('/retours')?.title).toBe('Livraison et retours')
    expect(legalDocumentByPath('/unknown')).toBeUndefined()
    expect(legalDocumentByPath('/cgv', 'en')?.title).toBe('Terms of sale')
  })
})

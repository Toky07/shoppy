import { describe, expect, it } from 'vitest'
import en from '../locales/en.json'
import fr from '../locales/fr.json'

function keys(tree: unknown, prefix = ''): string[] {
  if (tree === null || typeof tree !== 'object') {
    return prefix === '' ? [] : [prefix]
  }

  return Object.entries(tree as Record<string, unknown>).flatMap(([key, value]) => {
    const path = prefix === '' ? key : `${prefix}.${key}`
    return typeof value === 'object' && value !== null ? keys(value, path) : [path]
  })
}

describe('locale catalogs', () => {
  it('keeps french and english keys in sync', () => {
    expect(keys(en).sort()).toEqual(keys(fr).sort())
  })
})

import { describe, expect, it } from 'vitest'
import { stockLabel } from '../stockLabel'

describe('stockLabel', () => {
  it('shows remaining stock', () => {
    expect(stockLabel(10)).toBe('En stock (10)')
  })

  it('shows an out of stock label', () => {
    expect(stockLabel(0)).toBe('Rupture de stock')
  })
})

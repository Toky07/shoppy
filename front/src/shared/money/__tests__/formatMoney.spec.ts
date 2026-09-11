import { describe, expect, it } from 'vitest'
import { formatMoney } from '../formatMoney'

describe('formatMoney', () => {
  it('formats euro cents for fr-FR', () => {
    expect(formatMoney({ cents: 1999, currency: 'EUR' })).toMatch(/19,99/)
  })

  it('formats zero', () => {
    expect(formatMoney({ cents: 0, currency: 'EUR' })).toMatch(/0,00/)
  })
})

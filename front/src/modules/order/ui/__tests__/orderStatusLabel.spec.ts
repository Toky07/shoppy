import { describe, expect, it } from 'vitest'
import { orderStatusLabel } from '../orderStatusLabel'

describe('orderStatusLabel', () => {
  it('labels order statuses in French', () => {
    expect(orderStatusLabel('pending')).toBe('En attente')
    expect(orderStatusLabel('paid')).toBe('Payée')
    expect(orderStatusLabel('cancelled')).toBe('Annulée')
  })
})

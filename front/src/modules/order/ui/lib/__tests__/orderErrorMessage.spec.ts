import { describe, expect, it } from 'vitest'
import { orderErrorMessage } from '../orderErrorMessage'

describe('orderErrorMessage', () => {
  it('maps known order and payment errors', () => {
    expect(orderErrorMessage({ code: 'order_not_found', message: 'x' })).toBe('Cette commande est introuvable.')
    expect(orderErrorMessage({ code: 'forbidden', message: 'x' })).toBe('Cette commande est introuvable.')
    expect(orderErrorMessage({ code: 'invalid_order_transition', message: 'x' })).toBe(
      'Cette commande ne peut plus être annulée.',
    )
    expect(orderErrorMessage({ code: 'payment_not_payable', message: 'x' })).toBe(
      'Cette commande ne peut plus être payée.',
    )
    expect(orderErrorMessage({ code: 'payment_charge_failed', message: 'x' })).toBe('Le paiement a échoué.')
    expect(orderErrorMessage({ code: 'payment_provider_not_configured', message: 'x' })).toBe('Le paiement a échoué.')
  })
})

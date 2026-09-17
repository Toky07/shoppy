import { describe, expect, it } from 'vitest'
import { cartErrorMessage } from '../cartErrorMessage'

describe('cartErrorMessage', () => {
  it('maps known cart errors', () => {
    expect(cartErrorMessage({ code: 'insufficient_product_stock', message: 'x' })).toBe(
      'Stock insuffisant pour ce produit.',
    )
    expect(cartErrorMessage({ code: 'empty_cart', message: 'x' })).toBe('Le panier est vide.')
    expect(cartErrorMessage({ code: 'cart_item_not_found', message: 'x' })).toBe(
      "Cet article n'est plus dans le panier.",
    )
    expect(cartErrorMessage({ code: 'unauthenticated', message: 'x' })).toBe(
      'Votre session a expiré. Reconnectez-vous pour continuer.',
    )
  })

  it('prefers the first validation violation', () => {
    expect(
      cartErrorMessage({
        code: 'validation_error',
        message: 'The request is invalid.',
        violations: [{ message: 'Quantity must be greater than 0.' }],
      }),
    ).toBe('Quantity must be greater than 0.')
  })
})

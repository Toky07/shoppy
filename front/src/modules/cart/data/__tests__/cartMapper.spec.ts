import { describe, expect, it } from 'vitest'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapCart, mapCartItem, mapCheckoutResult } from '../cartMapper'
import {
  cartTeeItem,
  createCartItemJson,
  createCartJson,
  createCheckoutJson,
  filledCart,
  pendingCheckout,
} from '../../testing/cartFixtures'

describe('mapCartItem', () => {
  it('maps a cart line', () => {
    expect(mapCartItem(createCartItemJson())).toEqual(cartTeeItem)
  })

  it('rejects an invalid line', () => {
    expect(() => mapCartItem({ productId: 'x' })).toThrow(InvalidResponseError)
  })
})

describe('mapCart', () => {
  it('maps a cart payload', () => {
    expect(mapCart(createCartJson())).toEqual(filledCart)
  })

  it('maps an empty cart', () => {
    expect(
      mapCart({
        id: null,
        customerId: filledCart.customerId,
        items: [],
        total: { cents: 0, currency: 'EUR' },
        updatedAt: null,
      }),
    ).toEqual({
      id: null,
      customerId: filledCart.customerId,
      items: [],
      total: { cents: 0, currency: 'EUR' },
      updatedAt: null,
    })
  })

  it('rejects an invalid payload', () => {
    expect(() => mapCart({ id: 'x' })).toThrow(InvalidResponseError)
  })
})

describe('mapCheckoutResult', () => {
  it('maps the order id and status', () => {
    expect(mapCheckoutResult(createCheckoutJson())).toEqual(pendingCheckout)
  })

  it('rejects an invalid payload', () => {
    expect(() => mapCheckoutResult({ id: 'x' })).toThrow(InvalidResponseError)
  })
})

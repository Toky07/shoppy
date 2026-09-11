import { describe, expect, it } from 'vitest'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { mapOrder, mapOrderItem, mapOrderPage } from '../orderMapper'
import {
  createOrderItemJson,
  createOrderJson,
  orderTeeItem,
  pendingOrder,
} from '../../testing/orderFixtures'

describe('mapOrderItem', () => {
  it('maps an order line', () => {
    expect(mapOrderItem(createOrderItemJson())).toEqual(orderTeeItem)
  })

  it('rejects an invalid line', () => {
    expect(() => mapOrderItem({ productId: 'x' })).toThrow(InvalidResponseError)
  })
})

describe('mapOrder', () => {
  it('maps an order payload', () => {
    expect(mapOrder(createOrderJson())).toEqual(pendingOrder)
  })

  it('rejects an invalid payload', () => {
    expect(() => mapOrder({ id: 'x' })).toThrow(InvalidResponseError)
  })
})

describe('mapOrderPage', () => {
  it('maps a paginated list', () => {
    expect(
      mapOrderPage({
        items: [createOrderJson()],
        page: 1,
        limit: 20,
        total: 1,
      }),
    ).toEqual({
      items: [pendingOrder],
      page: 1,
      limit: 20,
      total: 1,
    })
  })

  it('rejects an invalid list payload', () => {
    expect(() => mapOrderPage({ items: [] })).toThrow(InvalidResponseError)
  })
})

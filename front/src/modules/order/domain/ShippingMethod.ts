import type { Money } from '@/shared/money/Money'

export const STANDARD_SHIPPING_FEE_CENTS = 490
export const EXPRESS_SHIPPING_FEE_CENTS = 990
export const FREE_STANDARD_FROM_CENTS = 4900

export const SHIPPING_METHODS = [
  {
    code: 'standard',
    label: 'Standard',
    feeCents: STANDARD_SHIPPING_FEE_CENTS,
    freeFromCents: FREE_STANDARD_FROM_CENTS,
  },
  {
    code: 'express',
    label: 'Express',
    feeCents: EXPRESS_SHIPPING_FEE_CENTS,
    freeFromCents: null,
  },
] as const

export type ShippingMethodCode = (typeof SHIPPING_METHODS)[number]['code']

export type OrderShipping = {
  method: string
  label: string
  fee: Money
}

export function quoteShipping(code: ShippingMethodCode, merchandiseCents: number): OrderShipping {
  const method = SHIPPING_METHODS.find((candidate) => candidate.code === code) ?? SHIPPING_METHODS[0]
  const free = method.freeFromCents !== null && merchandiseCents >= method.freeFromCents

  return {
    method: method.code,
    label: method.label,
    fee: { cents: free ? 0 : method.feeCents, currency: 'EUR' },
  }
}

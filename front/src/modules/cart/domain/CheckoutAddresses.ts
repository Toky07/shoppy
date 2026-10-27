import type { PostalAddress } from '@/modules/order/domain/PostalAddress'
import type { ShippingMethodCode } from '@/modules/order/domain/ShippingMethod'

export type CheckoutLine = {
  productId: string
  quantity: number
  variantId?: string | null
}

export type CheckoutAddresses = {
  shippingAddress: PostalAddress
  billingSameAsShipping: boolean
  billingAddress?: PostalAddress
  shippingMethod: ShippingMethodCode
  email?: string
  items?: CheckoutLine[]
}

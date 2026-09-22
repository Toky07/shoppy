import type { PostalAddress } from '@/modules/order/domain/PostalAddress'

export type CheckoutAddresses = {
  shippingAddress: PostalAddress
  billingSameAsShipping: boolean
  billingAddress?: PostalAddress
}

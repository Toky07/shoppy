import type { Money } from '@/shared/money/Money'
import type { OrderItem } from './OrderItem'
import type { OrderStatus } from './OrderStatus'
import type { PostalAddress } from './PostalAddress'
import type { OrderShipping } from './ShippingMethod'

export type Order = {
  id: string
  customerId: string
  customerEmail: string
  status: OrderStatus
  items: OrderItem[]
  total: Money
  createdAt: string
  shippingAddress: PostalAddress | null
  billingAddress: PostalAddress | null
  shipping: OrderShipping | null
}

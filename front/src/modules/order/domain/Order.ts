import type { Money } from '@/shared/money/Money'
import type { OrderItem } from './OrderItem'
import type { OrderStatus } from './OrderStatus'
import type { PostalAddress } from './PostalAddress'

export type Order = {
  id: string
  customerId: string
  status: OrderStatus
  items: OrderItem[]
  total: Money
  createdAt: string
  shippingAddress: PostalAddress | null
  billingAddress: PostalAddress | null
}

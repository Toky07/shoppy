import type { Money } from '@/shared/money/Money'
import type { CartItem } from './CartItem'

export type Cart = {
  id: string | null
  customerId: string
  items: CartItem[]
  total: Money
  updatedAt: string | null
}

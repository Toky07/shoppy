import type { Money } from '@/shared/money/Money'

export type CartItem = {
  productId: string
  name: string
  quantity: number
  unitPrice: Money
  lineTotal: Money
  availableStock: number
}

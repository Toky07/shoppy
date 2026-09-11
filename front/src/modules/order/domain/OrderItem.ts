import type { Money } from '@/shared/money/Money'

export type OrderItem = {
  productId: string
  name: string
  quantity: number
  unitPrice: Money
  lineTotal: Money
}

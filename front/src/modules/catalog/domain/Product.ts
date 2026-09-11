import type { Money } from '@/shared/money/Money'

export type Product = {
  id: string
  name: string
  description: string | null
  price: Money
  stock: number
  imageUrl: string | null
  createdAt: string
}

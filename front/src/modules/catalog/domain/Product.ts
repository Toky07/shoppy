import type { Money } from '@/shared/money/Money'

export type Product = {
  id: string
  slug: string
  name: string
  description: string | null
  price: Money
  stock: number
  imageUrl: string | null
  imageUrls: string[]
  createdAt: string
}

import type { Money } from '@/shared/money/Money'
import type { Category } from './Category'

export type ProductVariant = {
  id: string
  sku: string
  size: string | null
  color: string | null
  stock: number
}

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
  category: Category | null
  sku: string
  variants: ProductVariant[]
}

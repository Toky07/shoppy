import type { Product } from './Product'

export type ProductPage = {
  items: Product[]
  page: number
  limit: number
  total: number
}

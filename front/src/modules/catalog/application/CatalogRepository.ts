import type { Category } from '../domain/Category'
import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'
import type { ProductSort } from './productSort'

export type ListProductsQuery = {
  page: number
  limit: number
  search?: string
  sort?: ProductSort
  minPriceCents?: number
  maxPriceCents?: number
  inStockOnly?: boolean
  categorySlug?: string
}

export interface CatalogRepository {
  list(query: ListProductsQuery): Promise<ProductPage>
  listByIds(ids: string[]): Promise<Product[]>
  listCategories(): Promise<Category[]>
  getById(id: string): Promise<Product>
}

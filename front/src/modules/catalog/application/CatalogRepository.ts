import type { Product } from '../domain/Product'
import type { ProductPage } from '../domain/ProductPage'
import type { ProductSort } from './productSort'

export type ListProductsQuery = {
  page: number
  limit: number
  search?: string
  sort?: ProductSort
}

export interface CatalogRepository {
  list(query: ListProductsQuery): Promise<ProductPage>
  getById(id: string): Promise<Product>
}

import type { Product } from '../domain/Product'

export type CreateProductInput = {
  name: string
  priceCents: number
  description: string | null
  stock: number
}

export type UpdateProductInput = {
  name?: string
  priceCents?: number
  description?: string | null
}

export interface AdminCatalogRepository {
  create(input: CreateProductInput): Promise<Product>
  update(id: string, input: UpdateProductInput): Promise<Product>
  setStock(id: string, stock: number): Promise<Product>
  delete(id: string): Promise<void>
}

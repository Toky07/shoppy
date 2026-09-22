import type { Product } from '../domain/Product'

export type ProductVariantInput = {
  id?: string
  sku: string
  size: string | null
  color: string | null
  stock: number
}

export type ProductImage = {
  id: string
  url: string
  position: number
}

export type CreateProductInput = {
  name: string
  priceCents: number
  description: string | null
  stock: number
  categoryId?: string | null
  sku?: string | null
  variants?: ProductVariantInput[]
  published?: boolean
}

export type UpdateProductInput = {
  name?: string
  priceCents?: number
  description?: string | null
  categoryId?: string | null
  sku?: string | null
  variants?: ProductVariantInput[]
  published?: boolean
}

export interface AdminCatalogRepository {
  create(input: CreateProductInput): Promise<Product>
  update(id: string, input: UpdateProductInput): Promise<Product>
  setStock(id: string, stock: number): Promise<Product>
  delete(id: string): Promise<void>
  listImages(productId: string): Promise<ProductImage[]>
  uploadImage(productId: string, file: File, position?: number): Promise<ProductImage>
  deleteImage(id: string): Promise<void>
  reorderImages(productId: string, ids: string[]): Promise<void>
}

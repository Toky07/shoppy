import { ApiError } from '@/shared/http/ApiError'
import type { AdminCatalogRepository, CreateProductInput, UpdateProductInput } from '../application/AdminCatalogRepository'
import type { Product } from '../domain/Product'
import { nuvoraTee } from './productFixtures'

function cloneProduct(product: Product): Product {
  return { ...product, price: { ...product.price }, imageUrls: [...product.imageUrls] }
}

export class FakeAdminCatalogRepository implements AdminCatalogRepository {
  public created: CreateProductInput[] = []
  public updated: Array<{ id: string; input: UpdateProductInput }> = []
  public stockSets: Array<{ id: string; stock: number }> = []
  public deleted: string[] = []
  public createResult: Product = nuvoraTee
  public createError: Error | null = null
  public updateError: Error | null = null
  public deleteError: Error | null = null
  public products: Product[]

  constructor(products: Product[] = []) {
    this.products = products.map(cloneProduct)
  }

  async create(input: CreateProductInput): Promise<Product> {
    this.created.push(input)
    if (this.createError) {
      throw this.createError
    }
    return cloneProduct(this.createResult)
  }

  async update(id: string, input: UpdateProductInput): Promise<Product> {
    this.updated.push({ id, input })
    if (this.updateError) {
      throw this.updateError
    }
    const product = this.products.find((item) => item.id === id)
    if (!product) {
      throw new ApiError(404, 'product_not_found', 'Product not found.')
    }
    const next: Product = {
      ...product,
      name: input.name ?? product.name,
      description: input.description === undefined ? product.description : input.description,
      price:
        input.priceCents === undefined
          ? product.price
          : { cents: input.priceCents, currency: product.price.currency },
    }
    this.products = this.products.map((item) => (item.id === id ? next : item))
    return cloneProduct(next)
  }

  async setStock(id: string, stock: number): Promise<Product> {
    this.stockSets.push({ id, stock })
    const product = this.products.find((item) => item.id === id)
    if (!product) {
      throw new ApiError(404, 'product_not_found', 'Product not found.')
    }
    const next = { ...product, stock }
    this.products = this.products.map((item) => (item.id === id ? next : item))
    return cloneProduct(next)
  }

  async delete(id: string): Promise<void> {
    this.deleted.push(id)
    if (this.deleteError) {
      throw this.deleteError
    }
    this.products = this.products.filter((item) => item.id !== id)
  }
}

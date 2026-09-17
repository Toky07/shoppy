import type { HttpClient } from '@/shared/http/HttpClient'
import type { AdminCatalogRepository, CreateProductInput, UpdateProductInput } from '../application/AdminCatalogRepository'
import type { Product } from '../domain/Product'
import { mapProduct } from './productMapper'

export class AdminCatalogHttpRepository implements AdminCatalogRepository {
  constructor(private readonly http: HttpClient) {}

  async create(input: CreateProductInput): Promise<Product> {
    return mapProduct(
      await this.http.post('/products', {
        name: input.name,
        priceCents: input.priceCents,
        description: input.description,
        stock: input.stock,
      }),
    )
  }

  async update(id: string, input: UpdateProductInput): Promise<Product> {
    const body: Record<string, unknown> = {}
    if (input.name !== undefined) {
      body.name = input.name
    }
    if (input.priceCents !== undefined) {
      body.priceCents = input.priceCents
    }
    if (input.description !== undefined) {
      body.description = input.description
    }
    return mapProduct(await this.http.patch(`/products/${encodeURIComponent(id)}`, body))
  }

  async setStock(id: string, stock: number): Promise<Product> {
    return mapProduct(await this.http.put(`/products/${encodeURIComponent(id)}/stock`, { stock }))
  }

  async delete(id: string): Promise<void> {
    await this.http.delete(`/products/${encodeURIComponent(id)}`)
  }
}

import type { HttpClient } from '@/shared/http/HttpClient'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'
import type { AdminCatalogRepository, CreateProductInput, ProductImage, UpdateProductInput } from '../application/AdminCatalogRepository'
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
        ...(input.categoryId !== undefined ? { categoryId: input.categoryId } : {}),
        ...(input.sku !== undefined ? { sku: input.sku } : {}),
        ...(input.variants !== undefined ? { variants: input.variants } : {}),
        ...(input.published !== undefined ? { published: input.published } : {}),
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
    if (input.categoryId !== undefined) {
      body.categoryId = input.categoryId
    }
    if (input.sku !== undefined) {
      body.sku = input.sku
    }
    if (input.variants !== undefined) {
      body.variants = input.variants
    }
    if (input.published !== undefined) {
      body.published = input.published
    }
    return mapProduct(await this.http.patch(`/products/${encodeURIComponent(id)}`, body))
  }

  async setStock(id: string, stock: number): Promise<Product> {
    return mapProduct(await this.http.put(`/products/${encodeURIComponent(id)}/stock`, { stock }))
  }

  async delete(id: string): Promise<void> {
    await this.http.delete(`/products/${encodeURIComponent(id)}`)
  }

  async listImages(productId: string): Promise<ProductImage[]> {
    const payload = await this.http.get('/media', { ownerType: 'product', ownerId: productId })

    if (!isMediaList(payload)) {
      throw new InvalidResponseError('Invalid media payload.')
    }

    return payload.items
      .map((item) => ({ id: item.id, url: item.url, position: item.position }))
      .sort((left, right) => left.position - right.position)
  }

  async uploadImage(productId: string, file: File, position?: number): Promise<ProductImage> {
    const body = new FormData()
    body.append('file', file)
    body.append('ownerType', 'product')
    body.append('ownerId', productId)
    if (position !== undefined) {
      body.append('position', String(position))
    }
    const payload = await this.http.postForm('/media', body)

    if (!isMedia(payload)) {
      throw new InvalidResponseError('Invalid media payload.')
    }

    return { id: payload.id, url: payload.url, position: payload.position }
  }

  async deleteImage(id: string): Promise<void> {
    await this.http.delete(`/media/${encodeURIComponent(id)}`)
  }

  async reorderImages(productId: string, ids: string[]): Promise<void> {
    await this.http.put('/media/order', { ownerType: 'product', ownerId: productId, ids })
  }
}

function isMedia(payload: unknown): payload is ProductImage {
  return isRecord(payload) && typeof payload.id === 'string' && typeof payload.url === 'string' && typeof payload.position === 'number'
}

function isMediaList(payload: unknown): payload is { items: ProductImage[] } {
  return isRecord(payload) && Array.isArray(payload.items) && payload.items.every(isMedia)
}

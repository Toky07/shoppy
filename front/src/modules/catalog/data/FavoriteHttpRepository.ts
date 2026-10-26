import type { HttpClient } from '@/shared/http/HttpClient'
import type { FavoriteRepository } from '../application/FavoriteRepository'

type FavoriteListJson = {
  productIds?: unknown
}

function productIds(payload: FavoriteListJson): string[] {
  return Array.isArray(payload.productIds)
    ? payload.productIds.filter((id): id is string => typeof id === 'string')
    : []
}

export class FavoriteHttpRepository implements FavoriteRepository {
  constructor(private readonly http: HttpClient) {}

  async list(): Promise<string[]> {
    return productIds(await this.http.get<FavoriteListJson>('/favorites'))
  }

  async add(productId: string): Promise<string[]> {
    return productIds(await this.http.post<FavoriteListJson>('/favorites', { productId }))
  }

  async remove(productId: string): Promise<string[]> {
    return productIds(await this.http.delete<FavoriteListJson>(`/favorites/${encodeURIComponent(productId)}`))
  }

  async clear(): Promise<string[]> {
    return productIds(await this.http.delete<FavoriteListJson>('/favorites'))
  }

  async merge(ids: string[]): Promise<string[]> {
    return productIds(await this.http.post<FavoriteListJson>('/favorites/merge', { productIds: ids }))
  }
}

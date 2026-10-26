import type { FavoriteRepository } from '../application/FavoriteRepository'

export class FakeFavoriteRepository implements FavoriteRepository {
  public productIds: string[]
  public added: string[] = []
  public removed: string[] = []
  public merged: string[] = []
  public listCount = 0
  public clearCount = 0

  constructor(productIds: string[] = []) {
    this.productIds = [...productIds]
  }

  async list(): Promise<string[]> {
    this.listCount += 1
    return [...this.productIds]
  }

  async add(productId: string): Promise<string[]> {
    this.added.push(productId)
    if (!this.productIds.includes(productId)) {
      this.productIds = [productId, ...this.productIds]
    }

    return [...this.productIds]
  }

  async remove(productId: string): Promise<string[]> {
    this.removed.push(productId)
    this.productIds = this.productIds.filter((id) => id !== productId)
    return [...this.productIds]
  }

  async clear(): Promise<string[]> {
    this.clearCount += 1
    this.productIds = []
    return []
  }

  async merge(productIds: string[]): Promise<string[]> {
    this.merged.push(...productIds)
    const seen = new Set<string>()
    const next: string[] = []

    for (const id of [...productIds, ...this.productIds]) {
      if (seen.has(id)) {
        continue
      }
      seen.add(id)
      next.push(id)
    }

    this.productIds = next
    return [...this.productIds]
  }
}

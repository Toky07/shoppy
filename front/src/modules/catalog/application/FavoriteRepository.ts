export interface FavoriteRepository {
  list(): Promise<string[]>
  add(productId: string): Promise<string[]>
  remove(productId: string): Promise<string[]>
  clear(): Promise<string[]>
  merge(productIds: string[]): Promise<string[]>
}

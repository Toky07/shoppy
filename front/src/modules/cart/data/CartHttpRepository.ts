import type { HttpClient } from '@/shared/http/HttpClient'
import type { CartRepository } from '../application/CartRepository'
import type { Cart } from '../domain/Cart'
import type { CheckoutResult } from '../domain/CheckoutResult'
import { mapCart, mapCheckoutResult } from './cartMapper'

export class CartHttpRepository implements CartRepository {
  constructor(private readonly http: HttpClient) {}

  async get(): Promise<Cart> {
    return mapCart(await this.http.get('/cart'))
  }

  async addItem(productId: string, quantity: number): Promise<Cart> {
    return mapCart(await this.http.post('/cart/items', { productId, quantity }))
  }

  async updateItem(productId: string, quantity: number): Promise<Cart> {
    return mapCart(await this.http.put(`/cart/items/${encodeURIComponent(productId)}`, { quantity }))
  }

  async removeItem(productId: string): Promise<Cart> {
    return mapCart(await this.http.delete(`/cart/items/${encodeURIComponent(productId)}`))
  }

  async clear(): Promise<Cart> {
    return mapCart(await this.http.delete('/cart'))
  }

  async checkout(): Promise<CheckoutResult> {
    return mapCheckoutResult(await this.http.post('/cart/checkout'))
  }
}

import type { HttpClient } from '@/shared/http/HttpClient'
import type { CartRepository } from '../application/CartRepository'
import type { Cart } from '../domain/Cart'
import type { CheckoutAddresses } from '../domain/CheckoutAddresses'
import type { CheckoutResult } from '../domain/CheckoutResult'
import { mapCart, mapCheckoutResult } from './cartMapper'

export class CartHttpRepository implements CartRepository {
  constructor(private readonly http: HttpClient) {}

  async get(): Promise<Cart> {
    return mapCart(await this.http.get('/cart'))
  }

  async addItem(productId: string, quantity: number, variantId?: string | null): Promise<Cart> {
    return mapCart(
      await this.http.post('/cart/items', {
        productId,
        quantity,
        ...(variantId ? { variantId } : {}),
      }),
    )
  }

  async updateItem(productId: string, quantity: number, variantId?: string | null): Promise<Cart> {
    return mapCart(
      await this.http.put(`/cart/items/${encodeURIComponent(productId)}`, {
        quantity,
        ...(variantId ? { variantId } : {}),
      }),
    )
  }

  async removeItem(productId: string, variantId?: string | null): Promise<Cart> {
    const query = variantId ? `?variantId=${encodeURIComponent(variantId)}` : ''
    return mapCart(await this.http.delete(`/cart/items/${encodeURIComponent(productId)}${query}`))
  }

  async clear(): Promise<Cart> {
    return mapCart(await this.http.delete('/cart'))
  }

  async checkout(addresses: CheckoutAddresses): Promise<CheckoutResult> {
    return mapCheckoutResult(await this.http.post('/cart/checkout', addresses))
  }
}

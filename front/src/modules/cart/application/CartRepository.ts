import type { Cart } from '../domain/Cart'
import type { CheckoutResult } from '../domain/CheckoutResult'

export interface CartRepository {
  get(): Promise<Cart>
  addItem(productId: string, quantity: number): Promise<Cart>
  updateItem(productId: string, quantity: number): Promise<Cart>
  removeItem(productId: string): Promise<Cart>
  clear(): Promise<Cart>
  checkout(): Promise<CheckoutResult>
}

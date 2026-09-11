import type { CartRepository } from '../application/CartRepository'
import type { Cart } from '../domain/Cart'
import type { CartItem } from '../domain/CartItem'
import type { CheckoutResult } from '../domain/CheckoutResult'
import { emptyCart, pendingCheckout } from './cartFixtures'

function cloneCart(cart: Cart): Cart {
  return {
    ...cart,
    items: cart.items.map((item) => ({
      ...item,
      unitPrice: { ...item.unitPrice },
      lineTotal: { ...item.lineTotal },
    })),
    total: { ...cart.total },
  }
}

function withTotals(cart: Cart): Cart {
  const items = cart.items.map((item) => ({
    ...item,
    lineTotal: {
      cents: item.unitPrice.cents * item.quantity,
      currency: item.unitPrice.currency,
    },
  }))
  const currency = items[0]?.unitPrice.currency ?? cart.total.currency
  const cents = items.reduce((sum, item) => sum + item.lineTotal.cents, 0)

  return {
    ...cart,
    id: cart.id ?? 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    items,
    total: { cents, currency },
  }
}

export class FakeCartRepository implements CartRepository {
  public cart: Cart
  public added: Array<{ productId: string; quantity: number }> = []
  public updated: Array<{ productId: string; quantity: number }> = []
  public removed: string[] = []
  public clearCount = 0
  public checkoutCount = 0
  public getCount = 0
  public addError: Error | null = null
  public updateError: Error | null = null
  public checkoutError: Error | null = null
  public checkoutResult: CheckoutResult = pendingCheckout

  constructor(cart: Cart = emptyCart()) {
    this.cart = cloneCart(cart)
  }

  async get(): Promise<Cart> {
    this.getCount += 1
    return cloneCart(this.cart)
  }

  async addItem(productId: string, quantity: number): Promise<Cart> {
    this.added.push({ productId, quantity })
    if (this.addError) {
      throw this.addError
    }

    const existing = this.cart.items.find((item) => item.productId === productId)
    if (existing) {
      existing.quantity += quantity
    } else {
      this.cart.items.push(this.newItem(productId, quantity))
    }

    this.cart = withTotals(this.cart)
    return cloneCart(this.cart)
  }

  async updateItem(productId: string, quantity: number): Promise<Cart> {
    this.updated.push({ productId, quantity })
    if (this.updateError) {
      throw this.updateError
    }

    this.cart = withTotals({
      ...this.cart,
      items: this.cart.items.map((item) =>
        item.productId === productId ? { ...item, quantity } : item,
      ),
    })
    return cloneCart(this.cart)
  }

  async removeItem(productId: string): Promise<Cart> {
    this.removed.push(productId)
    this.cart = withTotals({
      ...this.cart,
      items: this.cart.items.filter((item) => item.productId !== productId),
    })
    if (this.cart.items.length === 0) {
      this.cart = emptyCart(this.cart.customerId)
    }
    return cloneCart(this.cart)
  }

  async clear(): Promise<Cart> {
    this.clearCount += 1
    this.cart = emptyCart(this.cart.customerId)
    return cloneCart(this.cart)
  }

  async checkout(): Promise<CheckoutResult> {
    this.checkoutCount += 1
    if (this.checkoutError) {
      throw this.checkoutError
    }
    this.cart = emptyCart(this.cart.customerId)
    return { ...this.checkoutResult }
  }

  private newItem(productId: string, quantity: number): CartItem {
    return {
      productId,
      name: productId,
      quantity,
      unitPrice: { cents: 0, currency: 'EUR' },
      lineTotal: { cents: 0, currency: 'EUR' },
      availableStock: 99,
    }
  }
}

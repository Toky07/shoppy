import { ApiError } from '@/shared/http/ApiError'
import type { ListOrdersQuery, OrderRepository } from '../application/OrderRepository'
import type { Order } from '../domain/Order'
import type { OrderPage } from '../domain/OrderPage'

function cloneOrder(order: Order): Order {
  return {
    ...order,
    items: order.items.map((item) => ({
      ...item,
      unitPrice: { ...item.unitPrice },
      lineTotal: { ...item.lineTotal },
    })),
    total: { ...order.total },
  }
}

export class FakeOrderRepository implements OrderRepository {
  public orders: Order[]
  public cancelled: string[] = []
  public markedPaid: string[] = []
  public listAllCount = 0
  public cancelError: Error | null = null
  public markPaidError: Error | null = null

  constructor(orders: Order[] = []) {
    this.orders = orders.map(cloneOrder)
  }

  async list(query: ListOrdersQuery): Promise<OrderPage> {
    const start = (query.page - 1) * query.limit

    return {
      items: this.orders.slice(start, start + query.limit).map(cloneOrder),
      page: query.page,
      limit: query.limit,
      total: this.orders.length,
    }
  }

  async listAll(query: ListOrdersQuery): Promise<OrderPage> {
    this.listAllCount += 1
    return this.list(query)
  }

  async getById(id: string): Promise<Order> {
    const order = this.orders.find((item) => item.id === id)

    if (!order) {
      throw new ApiError(404, 'order_not_found', 'Order not found.')
    }

    return cloneOrder(order)
  }

  async cancel(id: string): Promise<Order> {
    this.cancelled.push(id)
    if (this.cancelError) {
      throw this.cancelError
    }

    const order = await this.getById(id)
    if (order.status !== 'pending') {
      throw new ApiError(409, 'invalid_order_transition', 'Order cannot be cancelled.')
    }

    const next = { ...order, status: 'cancelled' as const }
    this.replace(next)
    return cloneOrder(next)
  }

  async markPaid(id: string): Promise<Order> {
    this.markedPaid.push(id)
    if (this.markPaidError) {
      throw this.markPaidError
    }

    const order = await this.getById(id)
    if (order.status !== 'pending') {
      throw new ApiError(409, 'invalid_order_transition', 'Order cannot be marked paid.')
    }

    const next = { ...order, status: 'paid' as const }
    this.replace(next)
    return cloneOrder(next)
  }

  replace(order: Order) {
    this.orders = this.orders.map((item) => (item.id === order.id ? cloneOrder(order) : item))
  }
}

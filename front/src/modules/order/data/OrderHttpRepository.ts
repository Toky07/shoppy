import type { HttpClient } from '@/shared/http/HttpClient'
import type { ListOrdersQuery, OrderRepository } from '../application/OrderRepository'
import type { Order } from '../domain/Order'
import type { OrderPage } from '../domain/OrderPage'
import { mapOrder, mapOrderPage } from './orderMapper'

export class OrderHttpRepository implements OrderRepository {
  constructor(private readonly http: HttpClient) {}

  async list(query: ListOrdersQuery): Promise<OrderPage> {
    return mapOrderPage(await this.http.get('/orders', query))
  }

  async listAll(query: ListOrdersQuery): Promise<OrderPage> {
    return mapOrderPage(await this.http.get('/admin/orders', query))
  }

  async getById(id: string): Promise<Order> {
    return mapOrder(await this.http.get(`/orders/${encodeURIComponent(id)}`))
  }

  async cancel(id: string): Promise<Order> {
    return mapOrder(await this.http.post(`/orders/${encodeURIComponent(id)}/cancel`))
  }

  async markPaid(id: string): Promise<Order> {
    return mapOrder(await this.http.post(`/orders/${encodeURIComponent(id)}/mark-paid`))
  }
}

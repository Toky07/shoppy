import type { Order } from '../domain/Order'
import type { OrderPage } from '../domain/OrderPage'

export type ListOrdersQuery = {
  page: number
  limit: number
}

export interface OrderRepository {
  list(query: ListOrdersQuery): Promise<OrderPage>
  listAll(query: ListOrdersQuery): Promise<OrderPage>
  getById(id: string): Promise<Order>
  cancel(id: string): Promise<Order>
  markPaid(id: string): Promise<Order>
}

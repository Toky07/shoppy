import type { Order } from './Order'

export type OrderPage = {
  items: Order[]
  page: number
  limit: number
  total: number
}

import type { Payment } from '../domain/Payment'

export interface PaymentRepository {
  getByOrder(orderId: string): Promise<Payment>
  complete(orderId: string): Promise<Payment>
}

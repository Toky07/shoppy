import type { HttpClient } from '@/shared/http/HttpClient'
import type { PaymentRepository } from '../application/PaymentRepository'
import type { Payment } from '../domain/Payment'
import { mapPayment } from './paymentMapper'

export class PaymentHttpRepository implements PaymentRepository {
  constructor(private readonly http: HttpClient) {}

  async getByOrder(orderId: string): Promise<Payment> {
    return mapPayment(await this.http.get(`/payments/by-order/${encodeURIComponent(orderId)}`))
  }

  async complete(orderId: string): Promise<Payment> {
    return mapPayment(await this.http.post('/payments/complete', { orderId }))
  }
}

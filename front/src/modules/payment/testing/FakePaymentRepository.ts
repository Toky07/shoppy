import { ApiError } from '@/shared/http/ApiError'
import type { PaymentRepository } from '../application/PaymentRepository'
import type { Payment } from '../domain/Payment'

function clonePayment(payment: Payment): Payment {
  return {
    ...payment,
    amount: { ...payment.amount },
  }
}

export class FakePaymentRepository implements PaymentRepository {
  public payment: Payment | null
  public completed: string[] = []
  public completeError: Error | null = null
  public getError: Error | null = null
  public onComplete: ((orderId: string) => void) | null = null

  constructor(payment: Payment | null = null) {
    this.payment = payment ? clonePayment(payment) : null
  }

  async getByOrder(orderId: string): Promise<Payment> {
    if (this.getError) {
      throw this.getError
    }

    if (!this.payment || this.payment.orderId !== orderId) {
      throw new ApiError(404, 'payment_not_found', 'Payment not found.')
    }

    return clonePayment(this.payment)
  }

  async complete(orderId: string): Promise<Payment> {
    this.completed.push(orderId)
    if (this.completeError) {
      throw this.completeError
    }

    const current = await this.getByOrder(orderId)
    const next: Payment = {
      ...current,
      status: 'completed',
      completedAt: '2026-09-10T12:10:00+00:00',
    }
    this.payment = next
    this.onComplete?.(orderId)
    return clonePayment(next)
  }
}

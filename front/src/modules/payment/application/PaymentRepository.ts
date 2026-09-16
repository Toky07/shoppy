import type { Payment } from '../domain/Payment'
import type { PaymentCheckout } from '../domain/PaymentCheckout'
import type { StartCheckoutRequest } from './StartCheckoutRequest'

export interface PaymentRepository {
  getByOrder(orderId: string): Promise<Payment>
  complete(orderId: string): Promise<Payment>
  startCheckout(request: StartCheckoutRequest): Promise<PaymentCheckout>
}

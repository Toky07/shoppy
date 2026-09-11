import type { Money } from '@/shared/money/Money'
import type { PaymentStatus } from './PaymentStatus'

export type Payment = {
  id: string
  orderId: string
  customerId: string
  amount: Money
  status: PaymentStatus
  createdAt: string
  completedAt: string | null
}

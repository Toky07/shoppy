import type { PaymentStatus } from './PaymentStatus'

export type PaymentCheckout = {
  provider: string
  status: PaymentStatus
  completedImmediately: boolean
  redirectUrl: string | null
}

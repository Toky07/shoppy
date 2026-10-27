import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'
import type { PaymentStatus } from '../../domain/PaymentStatus'

const keys: Record<PaymentStatus, 'payment.pending' | 'payment.completed' | 'payment.cancelled'> = {
  pending: 'payment.pending',
  completed: 'payment.completed',
  cancelled: 'payment.cancelled',
}

export function paymentStatusLabel(status: PaymentStatus, t: Translate = translateFr): string {
  return t(keys[status])
}

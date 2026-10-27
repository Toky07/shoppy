import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'
import type { OrderStatus } from '../../domain/OrderStatus'

const keys: Record<OrderStatus, 'orders.pending' | 'orders.paid' | 'orders.cancelled'> = {
  pending: 'orders.pending',
  paid: 'orders.paid',
  cancelled: 'orders.cancelled',
}

export function orderStatusLabel(status: OrderStatus, t: Translate = translateFr): string {
  return t(keys[status])
}

import type { IconName } from '@/shared/ui/icons'
import type { OrderStatus } from '../domain/OrderStatus'

const styles: Record<OrderStatus, { badge: string; icon: IconName }> = {
  pending: { badge: 'badge-warning', icon: 'clock' },
  paid: { badge: 'badge-positive', icon: 'check-circle' },
  cancelled: { badge: 'badge-neutral', icon: 'close' },
}

export function orderStatusStyle(status: OrderStatus) {
  return styles[status]
}

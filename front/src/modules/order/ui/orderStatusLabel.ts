import type { OrderStatus } from '../domain/OrderStatus'

const labels: Record<OrderStatus, string> = {
  pending: 'En attente',
  paid: 'Payée',
  cancelled: 'Annulée',
}

export function orderStatusLabel(status: OrderStatus): string {
  return labels[status]
}

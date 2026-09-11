import type { PaymentStatus } from '../domain/PaymentStatus'

const labels: Record<PaymentStatus, string> = {
  pending: 'En attente de paiement',
  completed: 'Payé',
  cancelled: 'Annulé',
}

export function paymentStatusLabel(status: PaymentStatus): string {
  return labels[status]
}

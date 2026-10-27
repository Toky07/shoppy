import type { AuditAction, AuditEntry } from '../../domain/AuditEntry'

const LABELS: Record<AuditAction, string> = {
  role_changed: 'Rôle',
  price_changed: 'Prix',
  stock_changed: 'Stock',
  payment_changed: 'Paiement',
}

export function auditActionLabel(action: AuditAction): string {
  return LABELS[action]
}

export function auditDetail(entry: AuditEntry): string {
  const metadata = entry.metadata

  if (entry.action === 'role_changed') {
    return `${metadata.from ?? '?'} → ${metadata.to ?? '?'}`
  }

  if (entry.action === 'price_changed') {
    return `${formatCents(metadata.fromCents)} → ${formatCents(metadata.toCents)}`
  }

  if (entry.action === 'stock_changed') {
    return `${metadata.from ?? '?'} → ${metadata.to ?? '?'}`
  }

  return `${metadata.from ?? '?'} → ${metadata.to ?? '?'}`
}

function formatCents(value: unknown): string {
  if (typeof value !== 'number') {
    return '?'
  }

  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(value / 100)
}

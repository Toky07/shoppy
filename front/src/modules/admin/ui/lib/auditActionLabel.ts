import { translateFr } from '@/shared/i18n/catalogs'
import type { Translate } from '@/shared/i18n/translate'
import type { AuditAction, AuditEntry } from '../../domain/AuditEntry'

const KEYS: Record<AuditAction, 'admin.audit.role' | 'admin.audit.price' | 'admin.audit.stock' | 'admin.audit.payment'> = {
  role_changed: 'admin.audit.role',
  price_changed: 'admin.audit.price',
  stock_changed: 'admin.audit.stock',
  payment_changed: 'admin.audit.payment',
}

export function auditActionLabel(action: AuditAction, t: Translate = translateFr): string {
  return t(KEYS[action])
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

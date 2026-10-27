export const AUDIT_ACTIONS = ['role_changed', 'price_changed', 'stock_changed', 'payment_changed'] as const

export type AuditAction = (typeof AUDIT_ACTIONS)[number]

export type AuditSubjectType = 'user' | 'product' | 'order'

export type AuditEntry = {
  id: string
  action: AuditAction
  actorId: string | null
  actorEmail: string | null
  subjectType: AuditSubjectType
  subjectId: string
  metadata: Record<string, string | number | boolean | null>
  createdAt: string
}

export type AuditLogPage = {
  items: AuditEntry[]
  page: number
  limit: number
  total: number
}

export type ListAuditLogQuery = {
  page: number
  limit: number
}

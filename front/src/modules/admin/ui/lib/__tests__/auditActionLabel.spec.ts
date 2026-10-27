import { describe, expect, it } from 'vitest'
import type { AuditEntry } from '../../../domain/AuditEntry'
import { auditActionLabel, auditDetail } from '../auditActionLabel'

const base: Omit<AuditEntry, 'action' | 'metadata'> = {
  id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1',
  actorId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9',
  actorEmail: 'admin@nuvora.test',
  subjectType: 'user',
  subjectId: '11111111-1111-4111-8111-111111111111',
  createdAt: '2026-10-27T12:00:00+00:00',
}

describe('auditActionLabel', () => {
  it('labels tracked admin actions', () => {
    expect(auditActionLabel('role_changed')).toBe('Rôle')
    expect(auditActionLabel('price_changed')).toBe('Prix')
    expect(auditActionLabel('stock_changed')).toBe('Stock')
    expect(auditActionLabel('payment_changed')).toBe('Paiement')
  })

  it('summarises old and new values', () => {
    expect(auditDetail({ ...base, action: 'role_changed', metadata: { from: 'customer', to: 'admin' } })).toBe(
      'customer → admin',
    )
    expect(
      auditDetail({
        ...base,
        action: 'price_changed',
        subjectType: 'product',
        metadata: { fromCents: 1999, toCents: 2499 },
      }),
    ).toMatch(/19,99.*24,99/)
    expect(
      auditDetail({
        ...base,
        action: 'stock_changed',
        subjectType: 'product',
        metadata: { from: 10, to: 3 },
      }),
    ).toBe('10 → 3')
  })
})

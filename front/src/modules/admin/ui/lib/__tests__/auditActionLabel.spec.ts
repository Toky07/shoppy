import { describe, expect, it } from 'vitest'
import type { AuditEntry } from '../../../domain/AuditEntry'
import { auditActionLabel, auditDetail, auditSubject } from '../auditActionLabel'

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

  it('names the product, account or order the action applied to', () => {
    expect(
      auditSubject({
        ...base,
        action: 'price_changed',
        subjectType: 'product',
        subjectId: '550e8400-e29b-41d4-a716-446655440000',
        metadata: { fromCents: 1999, toCents: 2499, label: 'Nuvora Tee' },
      }),
    ).toBe('Nuvora Tee')
    expect(
      auditSubject({
        ...base,
        action: 'role_changed',
        metadata: { from: 'customer', to: 'admin', label: 'ada@nuvora.test' },
      }),
    ).toBe('ada@nuvora.test')
    expect(
      auditSubject({
        ...base,
        action: 'payment_changed',
        subjectType: 'order',
        subjectId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        metadata: { from: 'pending', to: 'paid', label: 'Nuvora Tee' },
      }),
    ).toBe('Commande #AAAAAAAA · Nuvora Tee')
    expect(
      auditSubject({
        ...base,
        action: 'payment_changed',
        subjectType: 'order',
        subjectId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        metadata: { from: 'pending', to: 'paid' },
      }),
    ).toBe('Commande #AAAAAAAA')
    expect(
      auditSubject({
        ...base,
        action: 'stock_changed',
        subjectType: 'product',
        subjectId: '550e8400-e29b-41d4-a716-446655440000',
        metadata: { from: 10, to: 3 },
      }),
    ).toBe('550E8400')
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

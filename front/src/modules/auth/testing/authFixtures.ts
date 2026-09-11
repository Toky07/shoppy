import type { User } from '../domain/User'
import type { Session } from '../domain/Session'

export const visitorUser: User = {
  id: '11111111-1111-4111-8111-111111111111',
  email: 'visitor@shoppy.test',
  role: 'customer',
  createdAt: '2026-08-20T12:00:00+00:00',
}

export const visitorSession: Session = {
  accessToken: 'a'.repeat(64),
  user: visitorUser,
}

export const adminUser: User = {
  id: '22222222-2222-4222-8222-222222222222',
  email: 'admin@shoppy.test',
  role: 'admin',
  createdAt: '2026-08-20T12:00:00+00:00',
}

export const adminSession: Session = {
  accessToken: 'b'.repeat(64),
  user: adminUser,
}

export function createUserJson(overrides: Record<string, unknown> = {}) {
  return {
    id: visitorUser.id,
    email: visitorUser.email,
    role: visitorUser.role,
    createdAt: visitorUser.createdAt,
    ...overrides,
  }
}
